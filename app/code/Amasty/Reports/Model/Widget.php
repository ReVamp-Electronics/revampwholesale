<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

class Widget extends AbstractModel
{
    const WIDGET_TOTAL_ORDERS = 'total_orders';
    const WIDGET_TOTAL_SALES = 'total_sales';
    const WIDGET_TOTAL_CUSTOMERS = 'total_customers';
    const WIDGET_TOTAL_ITEMS = 'total_items';
    const WIDGET_TOTAL_REFUNDED = 'total_refunded';
    const WIDGET_TOTAL_ABANDONED = 'total_abandoned';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configInterface;
    /**
     * @var CollectionFactory
     */
    private $ordersCollectionFactory;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $reportsHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customerCollection;
    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $saveConfigInterface;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $saveConfigInterface,
        CollectionFactory $ordersCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
        \Amasty\Reports\Helper\Data $reportsHelper,

        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->configInterface = $configInterface;
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->reportsHelper = $reportsHelper;

        $this->customerCollection = $customerCollection;
        $this->saveConfigInterface = $saveConfigInterface;
    }

    public function getCurrentWidgets()
    {
        $allWidgets = $this->getWidgets();
        $activeWidget1 = $this->getActiveWidget(1);
        $activeWidget2 = $this->getActiveWidget(2);
        $activeWidget3 = $this->getActiveWidget(3);
        $activeWidget4 = $this->getActiveWidget(4);

        return [
            '1' => $allWidgets[$activeWidget1],
            '2' => $allWidgets[$activeWidget2],
            '3' => $allWidgets[$activeWidget3],
            '4' => $allWidgets[$activeWidget4],
        ];
    }

    public function getWidgetData($widget)
    {
        $result = 0;
        switch ($widget) {
            case self::WIDGET_TOTAL_ITEMS:
                $collection = $this->ordersCollectionFactory
                    ->create()
                    ->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered');
                if ($this->reportsHelper->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
                }
                $result = $collection->fetchItem()->getTotalQtyOrdered();
                break;
            case self::WIDGET_TOTAL_CUSTOMERS:
                $collection = $this->customerCollection
                    ->removeAllFieldsFromSelect();
                if ($this->reportsHelper->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
                }
                $result = $collection->count();
                break;
            case self::WIDGET_TOTAL_REFUNDED:
                $collection = $this->ordersCollectionFactory
                    ->create()
                    ->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect('total_refunded', 'SUM({{total_refunded}})', 'total_refunded');
                if ($this->reportsHelper->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
                }
                $result = $collection->fetchItem()->getTotalRefunded();
                break;
            case self::WIDGET_TOTAL_ORDERS:
                $collection = $this->ordersCollectionFactory
                    ->create()
                    ->removeAllFieldsFromSelect()
                    ->addOrdersCount()
                ;
                if ($this->reportsHelper->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
                }
                $result = $collection->fetchItem()->getOrdersCount();
                break;
            case self::WIDGET_TOTAL_ABANDONED:
                $collection = $this->ordersCollectionFactory
                    ->create()
                    ->removeAllFieldsFromSelect()
                    ->addOrdersCount()
                ;
                $collection->addFieldToFilter('state', ['eq' => \Magento\Sales\Model\Order::STATE_PROCESSING]);
                if ($this->reportsHelper->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
                }
                $result = $collection->fetchItem()->getOrdersCount();
                break;
            case self::WIDGET_TOTAL_SALES:
                $collection = $this->ordersCollectionFactory
                    ->create()
                    ->removeAllFieldsFromSelect()
                    ->addExpressionFieldToSelect('base_grand_total', 'SUM({{base_grand_total}})', 'base_grand_total');
                if ($this->reportsHelper->getCurrentStoreId()) {
                    $collection->addFieldToFilter('store_id', $this->reportsHelper->getCurrentStoreId());
                }
                $result = $collection->fetchItem()->getBaseGrandTotal();
                break;
        }
        return $result;
    }

    public function getActiveWidget($number)
    {
        return $this->configInterface->getValue('amreports/widgets/widget'.$number);
    }

    public function changeWidget($number, $name)
    {
        return $this->saveConfigInterface->saveConfig('amreports/widgets/widget'.$number, $name, 'default', 0);
    }

    public function getWidgets()
    {
        return [
            self::WIDGET_TOTAL_ORDERS => [
                'name'  => self::WIDGET_TOTAL_ORDERS,
                'title' => __('Total Orders'),
                'icon'  => 'Amasty_Reports::img/widgets/orders.png',
                'link'  => 'amasty_reports/report_sales/orders'
            ],
            self::WIDGET_TOTAL_SALES => [
                'name'  => self::WIDGET_TOTAL_SALES,
                'title' => __('Total Sales'),
                'icon'  => 'Amasty_Reports::img/widgets/sales.png',
                'link'  => 'amasty_reports/report_sales/orders'
            ],
            self::WIDGET_TOTAL_CUSTOMERS => [
                'name'  => self::WIDGET_TOTAL_CUSTOMERS,
                'title' => __('Total Customers'),
                'icon'  => 'Amasty_Reports::img/widgets/customers.png',
                'link'  => 'amasty_reports/report_customers/customers'
            ],

            self::WIDGET_TOTAL_ITEMS => [
                'name'  => self::WIDGET_TOTAL_ITEMS,
                'title' => __('Total Items Ordered'),
                'icon'  => 'Amasty_Reports::img/widgets/items.png',
                'link'  => 'amasty_reports/report_sales/orders'
            ],
            self::WIDGET_TOTAL_REFUNDED => [
                'name'  => self::WIDGET_TOTAL_REFUNDED,
                'title' => __('Total Refunded'),
                'icon'  => 'Amasty_Reports::img/widgets/revenue.png',
                'link'  => 'amasty_reports/report_sales/orders'
            ],
            self::WIDGET_TOTAL_ABANDONED => [
                'name'  => self::WIDGET_TOTAL_ABANDONED,
                'title' => __('Total Abandoned Cart'),
                'icon'  => 'Amasty_Reports::img/widgets/abandoned.png',
                'link'  => 'amasty_reports/report_sales/orders'
            ]
        ];
    }
}