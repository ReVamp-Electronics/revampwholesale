<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

abstract class Report extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * Report constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory  $resultRawFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function prepareResponse()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();

            $rawContent = $resultPage->getLayout()->renderElement('amreports.report.content');
            $resultRaw->setContents($rawContent);
            
            return $resultRaw;
        }

        $resultPage->setActiveMenu('Amasty_Reports::reports');
        $resultPage->addBreadcrumb(__('Advanced Reports'), __('Advanced Reports'));
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Reports'));

        return $resultPage;
    }
}
