<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class SaveSell extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Save Sell Products in Points
     *
     * @return void
     */
    public function execute()
    {
    	$data = $this->getRequest()->getPost();
        if ($data) {
        	$backendSession = $this->_objectManager->get('Magento\Backend\Model\Session');
            $productActionModel = $this->_objectManager->get('Magento\Catalog\Model\Product\Action');

            try {
                foreach ($data['mw_reward_point_sell_product'] as $key => $value) {
                    if (substr_count($key, 'mw_') == 1 && $value != '') {
                        $product    = explode('mw_', $key);
                        $product_id = $product[1];
                        if ($value == 0) {
                            $value = '';
                        }

                        $productActionModel->updateAttributes(
                        	[$product_id],
                        	['mw_reward_point_sell_product' => $value],
                        	0
                        );
                    }
                }

                $this->messageManager->addSuccess(
                	__('The reward points has been saved successfully!')
                );
                $backendSession->setFormData(false);

                $this->_redirect('*/*/sell');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $backendSession->setFormData($data);
                $this->_redirect('*/*/sell');
                return;
            }
        }

        $this->messageManager->addError(__('Unable to find product to save'));
        $this->_redirect('*/*/sell');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products_sell');
    }
}
