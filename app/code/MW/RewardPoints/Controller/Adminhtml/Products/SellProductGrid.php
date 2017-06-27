<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class SellProductGrid extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Sell Products in Points grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products_sell');
    }
}
