<?php

namespace MW\RewardPoints\Controller\Adminhtml\Member;

class Index extends \MW\RewardPoints\Controller\Adminhtml\Member
{
    /**
     * Member account page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
    	/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Manager Members'));
        return $resultPage;
    }
}
