<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;

class Save extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var \Amasty\ShippingTableRates\Model\Method $modelMethod
         */
        $modelMethod = $this->_objectManager->get('Amasty\ShippingTableRates\Model\Method');
        /**
         * @var \Amasty\ShippingTableRates\Model\Rate $modelRate
         */
        $modelRate = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Rate');
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $modelMethod->setData($data);
            $modelMethod->setId($id);

            if (($data['min_rate'] > $data['max_rate']) && ($data['max_rate'] > 0) && ($data['min_rate'] > 0)) {
                $this->messageManager->addError('Minimal rate must be less than maximal rate, please check your restrictions');
                $this->_redirect('*/*/edit', ['id' => $modelMethod->getId()]);
                return;
            }

            try {
                $noFile = false;
                $this->prepareForSave($modelMethod);
                $modelMethod->save();
                if ($modelMethod->getData('import_clear')) {
                    $modelRate->deleteBy($modelMethod->getId());
                }

                try {
                    /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'import_file']
                    );
                } catch (\Exception $e) {
                    $noFile = true;
                }

                // import files
                if (!$noFile) {
                    $uploader->setAllowedExtensions('csv');
                    $fileData = $uploader->validateFile();

                    $fileName = $fileData['tmp_name'];
                    ini_set('auto_detect_line_endings', 1);

                    $errors = $modelRate->import($modelMethod->getId(), $fileName);
                    foreach ($errors as $err) {
                        $this->messageManager->addError($err);
                    }
                }

                $msg = __('Shipping rates have been successfully saved');
                $this->messageManager->addSuccess($msg);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $modelMethod->getId()]);
                } else {
                    $this->_redirect('*/*');
                }
            } catch (\Exception $e) {
                $errMessage = $e->getMessage();
                if ($errMessage == 'Disallowed file type.') {
                    $errMessage = $errMessage . ' Please use CSV format of file for import';
                }
                $this->messageManager->addError($errMessage);
                $this->_redirect('*/*/edit', ['id' => $id]);
            }
            return;
        }

        $this->messageManager->addError(__('Unable to find a record to save'));
        $this->_redirect('*/*');
    }

    public function prepareForSave($model)
    {
        $fields = ['stores', 'cust_groups', 'free_types'];
        foreach ($fields as $f){
            // convert data from array to string
            $val = $model->getData($f);
            $model->setData($f, '');
            if (is_array($val)){
                // need commas to simplify sql query
                $model->setData($f, ',' . implode(',', $val) . ',');
            }
        }
        return true;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
