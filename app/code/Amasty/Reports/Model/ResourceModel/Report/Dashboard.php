<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Report;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Helper;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Psr\Log\LoggerInterface;

class Dashboard extends AbstractReport
{
    /**
     * Aggregation key daily
     */
    const AGGREGATION_DAILY = 'report_viewed_product_aggregated_daily';

    /**
     * Aggregation key monthly
     */
    const AGGREGATION_MONTHLY = 'report_viewed_product_aggregated_monthly';

    /**
     * Aggregation key yearly
     */
    const AGGREGATION_YEARLY = 'report_viewed_product_aggregated_yearly';

    /**
     * Product resource instance
     *
     * @var Product
     */
    protected $_productResource;

    /**
     * Resource helper instance
     *
     * @var Helper
     */
    protected $_resourceHelper;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var Collection
     */
    private $productCollection;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $dataHelper;


    /**
     * Dashboard constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param \Amasty\Reports\Helper\Data $dataHelper
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param Product $productResource
     * @param Collection $productCollection
     * @param TimezoneInterface $timezone
     * @param Helper $resourceHelper
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        \Amasty\Reports\Helper\Data $dataHelper,
        Validator $timezoneValidator,
        DateTime $dateTime,
        Product $productResource,
        Collection $productCollection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Helper $resourceHelper,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
        $this->_productResource = $productResource;
        $this->_resourceHelper = $resourceHelper;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->productCollection = $productCollection;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('report_event', 'id');
    }

    public function getFunnel($from = null, $to = null)
    {
        $viewedCount = $this->getViewedProducts($from, $to);
        $addedCount = $this->getAddedProducts($from, $to);
        if (!$addedCount) {
            $addedCount = 0;
        }
        if (!$viewedCount) {
            $viewedCount = 0;
        }
        $allCount = $this->getProductCount();
        $orderedCount = $this->getOrderedCount($from, $to);
        if (!$orderedCount) {
            $orderedCount = 0;
        }
        $notViewed = $viewedCount - $addedCount;
        $viewedPercent = 100;
        if ($notViewed && $viewedCount) {
            $viewedPercent = $notViewed / $viewedCount * 100;
        }
        $abandoned = $addedCount - $orderedCount;
        $addedPercent = 100;
        if ($addedCount && $addedCount) {
            $addedPercent = $abandoned / $addedCount * 100;
        }

        return [
            'viewedCount'  => $viewedCount,
            'addedCount'   => $addedCount,
            'allCount'     => $allCount,
            'orderedCount' => $orderedCount,
            'notViewed'    => $notViewed,
            'viewedPercent'=> $viewedPercent,
            'addedPercent' => $addedPercent,
            'abandoned'    => $abandoned,
        ];
    }

    protected function getProductCount($store = null)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $size = $this->productCollection
            ->addStoreFilter($store)
            ->setVisibility([
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                ])
            ->getSize();
        return $size;
    }

    protected function getViewedProducts($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.event_id)');
        $columns = [
            'views_num' => $viewsNumExpr,
        ];
        $select->from(
            ['source_table' => $this->getTable('report_event')],
            $columns
        )->where(
            'source_table.event_type_id = ?',
            \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW
        )->where(
            'DATE(source_table.logged_at) >= ?',
            $from
        )->where(
            'DATE(source_table.logged_at) <= ?',
            $to
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $select->group('source_table.event_type_id');
        $row = $connection->fetchRow($select);
        return $row['views_num'];
    }

    protected function getAddedProducts($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.event_id)');
        $columns = [
            'added_num' => $viewsNumExpr,
        ];
        $select->from(
            ['source_table' => $this->getTable('report_event')],
            $columns
        )->where(
            'source_table.event_type_id = ?',
            \Magento\Reports\Model\Event::EVENT_PRODUCT_TO_CART
        )->where(
            'DATE(source_table.logged_at) >= ?',
            $from
        )->where(
            'DATE(source_table.logged_at) <= ?',
            $to
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $select->group('source_table.event_type_id');
        $row = $connection->fetchRow($select);
        return $row['added_num'];
    }

    protected function getOrderedCount($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.item_id)');
        $columns = [
            'ordered_num' => $viewsNumExpr,
        ];
        $select->from(
            ['source_table' => $this->getTable('sales_order_item')],
            $columns
        )->where(
            'DATE(source_table.created_at) >= ?',
            $from
        )->where(
            'DATE(source_table.created_at) <= ?',
            $to
        )->where(
            'source_table.parent_item_id IS NULL'
        )->group(
            'source_table.order_id'
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $row = $connection->fetchRow($select);
        return $row['ordered_num'];
    }

    protected function getAbandonedCart($from, $to)
    {
        $connection = $this->getConnection();
        $from = $this->dateTime->date('Y-m-d', $from);
        $to = $this->dateTime->date('Y-m-d', $to);
        $select = $connection->select();
        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.total_qty_ordered)');
        $columns = [
            'ordered_num' => $viewsNumExpr,
        ];
        $select->from(
            ['source_table' => $this->getTable('sales_order')],
            $columns
        )->where(
            'DATE(source_table.created_at) >= ?',
            $from
        )->where(
            'DATE(source_table.created_at) <= ?',
            $to
        )->where(
            'DATE(source_table.created_at) <= ?',
            $to
        )->where(
            'state = ?',
            \Magento\Sales\Model\Order::STATE_PROCESSING
        );
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $select->where('store_id = ?', $storeId);
        }
        $row = $connection->fetchRow($select);
        return $row['ordered_num'];
    }

    
}
