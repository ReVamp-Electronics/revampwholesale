<?php

namespace MW\RewardPoints\Controller\Adminhtml\Rewardpoints;

class Import extends \MW\RewardPoints\Controller\Adminhtml\Rewardpoints
{
	/**
     * Import Customer Reward Points page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Import Reward Points'));
        return $resultPage;
    }
}
