<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class ProductGrid extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Individual Reward Points grid
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
        return $this->_authorization->isAllowed('MW_RewardPoints::products');
    }
}
