<?php

namespace MW\RewardPoints\Controller\Adminhtml\Member;

class Transaction extends \MW\RewardPoints\Controller\Adminhtml\Member
{
    /**
     * Transaction grid ajax action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
