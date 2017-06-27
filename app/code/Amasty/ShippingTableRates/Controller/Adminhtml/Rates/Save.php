<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        Context $context
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var \Amasty\ShippingTableRates\Model\Rate $model
         */
        $model = $this->_objectManager->get('Amasty\ShippingTableRates\Model\Rate');

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->messageManager->addError(__('Unable to find a rate to save'));
            $this->_redirect('adminhtml/amtable_method/index');
            return;
        }

        $isValid = $this->_checkData($data, $id);

        if ($isValid) {
            try {
                $methodId = $model->getMethodId();
                if (!$methodId) {
                    $methodId = $data['method_id'];
                }

                /**
                 * @var \Amasty\ShippingTableRates\Helper\Data $helper
                 */
                $helper = $this->_objectManager->get('Amasty\ShippingTableRates\Helper\Data');
                $fullZipFrom = $helper->getDataFromZip($data['zip_from']);
                $fullZipTo = $helper->getDataFromZip($data['zip_to']);
                $data['num_zip_from'] = $fullZipFrom['district'];
                $data['num_zip_to'] = $fullZipTo['district'];
                $model->setData($data)->setId($id);
                $model->save();

                $msg = __('Rate has been successfully saved');
                $this->messageManager->addSuccess($msg);

                //fix for save and continue of new rates
                if (is_null($id)) {
                    $id = $model->getId();
                }

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amstrates/methods/edit',
                        [
                            'id' => $methodId,
                            'tab' => 'rates_section'
                        ]
                    );
                }

            } catch (\Exception $e) {
                $this->messageManager->addError(__('This rate already exist!'));
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id, 'method_id' => $methodId));
            }
        } else {
            $this->_redirect('*/*/edit', ['id' => $id]);
        }
    }

    protected function _checkData($data)
    {
        $isValid = true;

        $checkKeys = [
            ['weight_from', 'weight_to'],
            ['qty_from', 'qty_to'],
            ['price_from', 'price_to']
        ];

        $keysLabels = [
            'weight_from' => __('Weight From'),
            'weight_to' => __('Weight To'),
            'qty_from' => __('Qty From'),
            'qty_to' => __('Qty To'),
            'price_from' => __('Price From'),
            'price_to' => __('Price To'),
        ];

        foreach ($checkKeys as $keys) {
            if ($data[$keys[0]] > $data[$keys[1]]) {
                $this->messageManager->addError($keysLabels[$keys[0]] . ' ' . __('must be less than') . ' ' . $keysLabels[$keys[1]]);
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
