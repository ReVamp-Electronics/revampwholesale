<?php

namespace MW\RewardPoints\Controller\Adminhtml\Cartrules;

class Index extends \MW\RewardPoints\Controller\Adminhtml\Cartrules
{
    /**
     * Shopping Cart Reward Rule page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Shopping Cart Reward Rules'));
        return $resultPage;
    }
}
