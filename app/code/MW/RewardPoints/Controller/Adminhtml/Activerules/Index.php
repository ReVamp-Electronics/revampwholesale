<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

class Index extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
    /**
     * Manage Customer Behavior Rules page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Customer Behavior Rules'));
        return $resultPage;
    }
}
