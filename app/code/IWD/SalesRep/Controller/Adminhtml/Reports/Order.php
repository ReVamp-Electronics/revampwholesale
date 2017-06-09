<?php

namespace IWD\SalesRep\Controller\Adminhtml\Reports;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Order
 * @package IWD\SalesRep\Controller\Adminhtml\Reports
 */
class Order extends \Magento\Reports\Controller\Adminhtml\Report\AbstractReport
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param TimezoneInterface $timezone
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        TimezoneInterface $timezone,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $fileFactory, $dateFilter, $timezone);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('IWD_SalesRep::reposts_order');
        $resultPage->addBreadcrumb(__('SalesRep'), __('Sales Representatives Report'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Representatives Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_reports_order.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        return $resultPage;
    }
}
