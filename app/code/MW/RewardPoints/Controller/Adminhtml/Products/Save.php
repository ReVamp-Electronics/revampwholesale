<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class Save extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Save Individual Reward Points
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data) {
            $backendSession = $this->_objectManager->get('Magento\Backend\Model\Session');
            $productActionModel = $this->_objectManager->get('Magento\Catalog\Model\Product\Action');
            try {
                foreach ($data['reward_point_product'] as $key => $value) {
                    if (substr_count($key, 'mw_') == 1 && $value != '') {
                        $product = explode('mw_', $key);
                        $productId = $product[1];
                        if ($value == 0) {
                            $value = '';
                        }

                        $productActionModel->updateAttributes(
                            [$productId],
                            ['reward_point_product' => $value],
                            0
                        );
                    }
                }
                $this->messageManager->addSuccess(
                    __('The reward points has been saved successfully!')
                );
                $backendSession->setFormData(false);

                $this->_redirect('*/*/index');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $backendSession->setFormData($data);
                $this->_redirect('*/*/index');
                return;
            }
        }

        $this->messageManager->addError(__('Unable to find product to save'));
        $this->_redirect('*/*/index');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products');
    }
}
