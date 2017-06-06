<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Customfield;

class Index extends \Aheadworks\Rma\Controller\Adminhtml\Customfield
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Rma::home');
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Fields'));
        return $resultPage;
    }
}