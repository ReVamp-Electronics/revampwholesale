<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml;

use Amasty\Reports\Helper\Data;
use Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\System\Store;

class Dashboard extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \Amasty\Reports\Model\Dasboard
     */
    private $dashboardModel;
    /**
     * @var Store
     */
    private $systemStore;
    /**
     * @var FormFactory
     */
    private $formFactory;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var Collection
     */
    private $overviewCollection;
    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory
     */
    private $salesCollectionFactory;
    /**
     * @var \Amasty\Reports\Model\Widget
     */
    private $widgetModel;


    /**
     * Dashboard constructor.
     * @param Context $context
     * @param \Amasty\Reports\Model\Dashboard $dashboardModel
     * @param Collection $overviewCollection
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory $salesCollectionFactory
     
     * @param \Amasty\Reports\Model\Widget $widgetModel
     * @param Data $dataHelper
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Amasty\Reports\Model\Dashboard $dashboardModel,
        Collection $overviewCollection,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory $salesCollectionFactory,
        
        \Amasty\Reports\Model\Widget $widgetModel,
        Data $dataHelper,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        CollectionFactory $collectionFactory,
        array $data =[]
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->dashboardModel = $dashboardModel;
        $this->systemStore = $systemStore;
        $this->formFactory = $formFactory;
        $this->request = $context->getRequest();
        $this->dataHelper = $dataHelper;
        $this->overviewCollection = $overviewCollection;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->widgetModel = $widgetModel;
        
    }
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create([
            'data' => [
                'id' => 'report_toolbar',
                'action' => '',
            ]
        ]);

        $this->addControls($form);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getLastOrders()
    {
        return $this->dashboardModel->getLastOrders();
    }
    
    public function getConversionFunnel()
    {
        $from = date('Y-m-d', $this->dataHelper->getDefaultFromDate());
        $to = date('Y-m-d', $this->dataHelper->getDefaultToDate());
        return $this->dashboardModel->getConversionFunnel($from, $to);
    }

    public function getFromDate()
    {
        return date('Y-m-d', $this->dataHelper->getDefaultFromDate());
    }

    public function getToDate()
    {
        return date('Y-m-d', $this->dataHelper->getDefaultToDate());
    }

    public function getMonthFromDate()
    {
        return date('Y-m-d', strtotime('-1 month'));
    }
    
    public function getSalesCollection()
    {
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $this->getRequest()->setParams(
                ['amreports' => ['store' => $storeId]]
            );
        }
        return $this->overviewCollection->prepareCollection($this->salesCollectionFactory->create());
    }

    public function getWidgetsData($widget)
    {
        return $this->widgetModel->getWidgetData($widget);
    }

    public function getCurrentWidgets()
    {
        return $this->widgetModel->getCurrentWidgets();
    }
    
    public function getAllWidgets()
    {
        return $this->widgetModel->getWidgets();
    }
    
    public function getBestsellers()
    {
        $this->getRequest()->setParams(
            ['amreports' => ['to' => $this->getToDate(), 'from' => $this->getMonthFromDate()]]
        );
        return $this->dashboardModel->getBestsellers();
    }

    protected function addControls(AbstractForm $form)
    {
        $form->addField('store', 'select', [
            'name'      => 'store',
            'values'    => $this->systemStore->getStoreValuesForForm(false, false),
            'class'     => 'right',
            'no_span'   => true,
            'value'     => $this->dataHelper->getCurrentStoreId()
        ]);

        return $this;
    }

    public function getTotalOrders()
    {

    }

    public function getTotalSales()
    {

    }

    public function getTotalRevenue()
    {

    }

    public function getTotalCustomers()
    {

    }
}
