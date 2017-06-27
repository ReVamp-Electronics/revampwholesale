<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

class Index extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
    /**
     * Catalog Reward Rules page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_RewardPoints::rewardpoints');
        $resultPage->getConfig()->getTitle()->prepend(__('Catalog Reward Rules'));
        return $resultPage;
    }
}
