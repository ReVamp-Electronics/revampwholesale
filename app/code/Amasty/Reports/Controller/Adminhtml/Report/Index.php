<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Amasty\Reports\Model\Dashboard;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\RequestInterface;

class Index extends ReportController
{
    /**
     * @var Dashboard
     */
    private $dashboardModel;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Data
     */
    private $jsonHelper;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Amasty\Reports\Model\Widget
     */
    private $widgetModel;
    /**
     * @var \Amasty\Reports\Block\Adminhtml\Dashboard
     */
    private $dashboardBlock;
    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection
     */
    private $overviewCollection;
    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory
     */
    private $salesCollectionFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        RawFactory $resultRawFactory,
        \Amasty\Reports\Helper\Data $dataHelper,
        \Amasty\Reports\Block\Adminhtml\Dashboard $dashboardBlock,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection $overviewCollection,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory $salesCollectionFactory,
        Data $jsonHelper,
        Dashboard $dashboardModel,
        \Amasty\Reports\Model\Widget $widgetModel
    ) {
        parent::__construct($context, $resultPageFactory, $resultRawFactory);
        $this->dashboardModel = $dashboardModel;
        $this->request = $context->getRequest();
        $this->jsonHelper = $jsonHelper;
        $this->jsonFactory = $jsonFactory;
        $this->dataHelper = $dataHelper;
        $this->widgetModel = $widgetModel;
        $this->dashboardBlock = $dashboardBlock;
        $this->overviewCollection = $overviewCollection;
        $this->salesCollectionFactory = $salesCollectionFactory;
    }

    public function execute()
    {
        $this->_request->getActionName();
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $jsonFactory = $this->jsonFactory->create();

        if ($this->prepareDashboard($jsonFactory)) {
            return $jsonFactory;
        }

        $this->prepareResponse();

        $resultPage->setActiveMenu('Amasty_Reports::reports');
        $resultPage->addBreadcrumb(__('Advanced Reports'), __('Advanced Reports'));
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Reports'));

        return $resultPage;
    }

    protected function prepareDashboard($jsonFactory)
    {
        $isDashboard = 0;
        if ($this->getRequest()->isAjax()) {
            switch ($this->getRequest()->getParam('amaction')) {
                case 'funnel':
                    $jsonFactory->setData($this->createFunnelData());
                    $isDashboard = 1;
                    break;
                case 'widget':
                    $data = $this->getRequest()->getParam('amreports');
                    $this->changeWidget($data['parent'], $data['widget']);
                    $jsonFactory->setData($this->getWidgetData($data['widget']));
                    $isDashboard = 1;
                    break;
                case 'sales':
                    if ($storeId = $this->dataHelper->getCurrentStoreId()) {
                        $this->getRequest()->setParams(
                            ['amreports' => ['store' => $storeId]]
                        );
                    }
                    $collection = $this->overviewCollection->prepareCollection($this->salesCollectionFactory->create());
                    $jsonFactory->setData($collection);
                    $isDashboard = 1;
                    break;
            }
        }

        $filters = $this->request->getParam('amreports');
        if ($this->getRequest()->isAjax() && isset($filters['store'])) {
            $isDashboard = 1;
            $this->dataHelper->setCurrentStore($filters['store']);
            $jsonFactory->setData(['success' => 1]);
        }
        return $isDashboard;
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
    
    protected function createFunnelData()
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['funnel_from']) ? $filters['funnel_from'] : null;
        $to = isset($filters['funnel_to']) ? $filters['funnel_to'] : null;
        return $this->jsonHelper->jsonEncode($this->dashboardModel->getConversionFunnel($from, $to));
    }
    
    protected function changeWidget($number, $name)
    {
        $this->widgetModel->changeWidget($number, $name);
    }

    protected function getWidgetData($name)
    {
        $value = $this->widgetModel->getWidgetData($name);
        $allWidgets = $this->widgetModel->getWidgets();
        $allWidgets[$name]['value'] = $value;
        $allWidgets[$name]['icon'] = $this->dashboardBlock->getViewFileUrl($allWidgets[$name]['icon']);
        $allWidgets[$name]['link'] = $this->dashboardBlock->getUrl($allWidgets[$name]['link']);
        return $allWidgets[$name];
    }
}
