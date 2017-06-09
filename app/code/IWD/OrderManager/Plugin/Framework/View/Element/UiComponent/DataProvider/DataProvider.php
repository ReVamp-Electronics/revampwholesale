<?php

namespace IWD\OrderManager\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\App\ResourceConnection;

/**
 * Class DataProvider
 * @package IWD\OrderManager\Plugin\Framework\View\Element\UiComponent\DataProvider
 */
class DataProvider
{
    /**
     * @var MagentoDataProvider
     */
    private $subject;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $totals = [
        'defaultTotals' => [
            'total'    => ['order' => 0, 'page' => 0, 'label' => 'Total'],
            'subtotal' => ['order' => 0, 'page' => 0, 'label' => 'Subtotal'],
        ],
        'additionalTotals' => [
            'tax'      => ['order' => 0, 'page' => 0, 'label' => 'Tax'],
            'invoiced' => ['order' => 0, 'page' => 0, 'label' => 'Invoiced'],
            'shipped'  => ['order' => 0, 'page' => 0, 'label' => 'Shipping'],
            'refunded' => ['order' => 0, 'page' => 0, 'label' => 'Refunds'],
            'discount' => ['order' => 0, 'page' => 0, 'label' => 'Coupons']
        ]
    ];

    /**
     * DataProvider constructor.
     * @param PricingHelper $pricingHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        PricingHelper $pricingHelper,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return bool
     */
    private function isGridTotalsEnabled()
    {
        return (bool)$this->scopeConfig->getValue('iwdordermanager/order_grid/order_grid_enable');
    }

    /**
     * @return bool
     */
    private function isOrderGridDataSource()
    {
        return $this->subject->getName() == 'sales_order_grid_data_source';
    }

    /**
     * @param MagentoDataProvider $subject
     * @param Filter $filter
     */
    public function beforeAddFilter(MagentoDataProvider $subject, Filter $filter)
    {
        $field = $filter->getField();
        $field = (strpos($field, 'main_table') === false) ? 'main_table.' . $field : $field;
        $filter->setField($field);
    }

    /**
     * @param MagentoDataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(MagentoDataProvider $subject, $result)
    {
        $this->subject = $subject;

        if ($this->isGridTotalsEnabled() && $this->isOrderGridDataSource()) {
            $result['iwdTotals'] = $this->getTotals();
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getTotals()
    {
        $this->prepareTotalOptions();
        $this->prepareTotals();

        return $this->totals;
    }

    /**
     * @return void
     */
    private function prepareTotalOptions()
    {
        $searchResult = $this->subject->getSearchResult();
        $searchCriteria = $this->subject->getSearchCriteria();

        $pageSize = $searchCriteria->getPageSize();
        $getCurPage = $searchCriteria->getCurrentPage();
        $to = $pageSize * $getCurPage;
        $size = $searchResult->getTotalCount();

        $this->totals['options'] = [
            'pageFrom' => $pageSize * ($getCurPage - 1) + 1,
            'pageTo' => ($to > $size) ? $size : $to,
            'ordersCount' => $size
        ];
    }

    /**
     * @return void
     */
    private function prepareTotals()
    {
        $searchResult = $this->prepareAdditionalSearchResult();

        $pageTotalsSelect = $this->preparePageTotalsSelect($searchResult);
        $pageTotals = $this->getSumTotals($searchResult, $pageTotalsSelect);

        $orderTotalsSelect = $this->prepareOrderTotalsSelect($searchResult);
        $orderTotals = $this->getSumTotals($searchResult, $orderTotalsSelect);

        foreach ($this->totals['defaultTotals'] as $key => $val) {
            $this->totals['defaultTotals'][$key]['order'] = $this->currencyFormat($orderTotals[$key]);
            $this->totals['defaultTotals'][$key]['page'] = $this->currencyFormat($pageTotals[$key]);
        }

        foreach ($this->totals['additionalTotals'] as $key => $val) {
            if (!$this->isAdditionalTotalEnabled($key)) {
                unset($this->totals['additionalTotals'][$key]);
                continue;
            }

            $this->totals['additionalTotals'][$key]['page'] = $this->currencyFormat($pageTotals[$key]);
            $this->totals['additionalTotals'][$key]['order'] = $this->currencyFormat($orderTotals[$key]);
        }
    }

    /**
     * @param $set
     * @return bool
     */
    private function isAdditionalTotalEnabled($set)
    {
        $sets = $this->scopeConfig->getValue('iwdordermanager/order_grid/order_grid_sets');
        $sets = explode(',', $sets);

        return in_array($set, $sets);
    }

    /**
     * @param $amount
     * @return string
     */
    private function currencyFormat($amount)
    {
        return $this->pricingHelper->currency($amount, true, false);
    }

    /**
     * @param $searchResult SearchResultInterface|\Magento\Framework\Data\Collection\AbstractDb
     * @return mixed
     */
    private function prepareOrderTotalsSelect($searchResult)
    {
        $select = clone $searchResult->getSelect();
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        return $select;
    }

    /**
     * @param $searchResult SearchResultInterface|\Magento\Framework\Data\Collection\AbstractDb
     * @return mixed
     */
    private function preparePageTotalsSelect($searchResult)
    {
        $select = clone $searchResult->getSelect();

        $searchCriteria = $this->subject->getSearchCriteria();
        $pageSize = $searchCriteria->getPageSize();
        $getCurPage = $searchCriteria->getCurrentPage();
        $select->limitPage($getCurPage, $pageSize);
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            if ($sortOrder->getField()) {
                $field = $sortOrder->getField();
                $field = (strpos($field, 'main_table') === false) ? 'main_table.' . $field : $field;
                $select->order(new \Zend_Db_Expr($field . ' ' . $sortOrder->getDirection()));
            }
        }

        return $select;
    }

    /**
     * @param $searchResult SearchResultInterface|\Magento\Framework\Data\Collection\AbstractDb
     * @param $select
     * @return mixed
     */
    private function getSumTotals($searchResult, $select)
    {
        $countSelect = clone $searchResult->getSelect();
        $countSelect->reset();
        $countSelect->from(
            [
                'a' => $select
            ],
            [
                'total'     => 'SUM(total_amount)',
                'subtotal'  => 'SUM(subtotal_amount)',
                'shipped'   => 'SUM(shipped_amount)',
                'tax'       => 'SUM(tax_amount)',
                'invoiced'  => 'SUM(invoiced_amount)',
                'discount'  => 'SUM(discount_amount)',
                'refunded'  => 'SUM(refunded_amount)'
            ]
        );

        return $searchResult->getConnection()->fetchRow($countSelect);
    }

    /**
     * @return SearchResultInterface|\Magento\Framework\Data\Collection\AbstractDb
     */
    private function prepareAdditionalSearchResult()
    {
        $searchResult = $this->subject->getSearchResult();

        /**
         * @var $searchResult \Magento\Framework\Data\Collection\AbstractDb
         */
        $salesOrderTable = $this->resourceConnection->getTableName('sales_order');
        $searchResult->getSelect()->joinLeft(
            ['sales_order' => $salesOrderTable],
            'main_table.entity_id = sales_order.entity_id',
            [
                'total_amount' => 'sales_order.base_grand_total',
                'subtotal_amount' => 'sales_order.base_subtotal',
                'invoiced_amount' => 'sales_order.base_total_invoiced',
                'shipped_amount' => 'sales_order.base_shipping_amount',
                'tax_amount' => 'sales_order.base_tax_amount',
                'discount_amount' => 'sales_order.base_discount_amount',
                'refunded_amount' => 'sales_order.base_total_refunded'
            ]
        );

        return $searchResult;
    }
}
