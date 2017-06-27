<?php

namespace MW\RewardPoints\Controller\Adminhtml\History;

class Index extends \MW\RewardPoints\Controller\Adminhtml\History
{
    /**
     * All Transaction History page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('All Transaction History'));
        return $resultPage;
    }
}
