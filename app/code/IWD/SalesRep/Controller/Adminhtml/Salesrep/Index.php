<?php

namespace IWD\SalesRep\Controller\Adminhtml\Salesrep;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package IWD\SalesRep\Controller\Adminhtml\Salesrep
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('IWD_SalesRep::salesrep');
        $resultPage->addBreadcrumb(__('SalesRep'), __('Sales Representatives'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Representatives'));

        return $resultPage;
    }
}
