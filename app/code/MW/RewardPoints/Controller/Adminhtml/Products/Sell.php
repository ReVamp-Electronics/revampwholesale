<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class Sell extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Individual Reward Points page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Sell Products in Points'));
        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products_sell');
    }
}
