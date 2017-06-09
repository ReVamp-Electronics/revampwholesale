<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class ImportProductPoints extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Import Reward Points page
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Import Product Reward Points'));
        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products');
    }
}
