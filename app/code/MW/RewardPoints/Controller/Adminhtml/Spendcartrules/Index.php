<?php

namespace MW\RewardPoints\Controller\Adminhtml\Spendcartrules;

class Index extends \MW\RewardPoints\Controller\Adminhtml\Spendcartrules
{
    /**
     * Shopping Cart Spending Rule page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward Points Spending Rules'));
        return $resultPage;
    }
}
