<?php

namespace IWD\OrderManager\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package IWD\OrderManager\Controller\Adminhtml\Log
 */
class Index extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_log';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('IWD Order Manager Log'));

        return $resultPage;
    }

    /**
     * Init layout, menu and breadcrumb
     * @return \Magento\Backend\Model\View\Result\Page
     */
    private function initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_iwd_om_log')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('IWD Order Manager'), __('IWD Order Manager'))
            ->addBreadcrumb(__('Log'), __('Log'));
        return $resultPage;
    }
}
