<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Category;

use Amasty\Reports\Traits\Filters;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory                  $entityFactory
     * @param \Psr\Log\LoggerInterface                                          $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface      $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                         $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper                                      $coreResourceHelper
     * @param \Magento\Framework\App\RequestInterface                           $request
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null               $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null         $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
    }

    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    public function joinCategoryTable($collection)
    {
        $collection->getSelect()
            ->join(
                ['sales_order_item' => $this->getTable('sales_order_item')],
                'sales_order_item.order_id = main_table.entity_id'
            )
            ->join(
                ['catalog_category_product' => $this->getTable('catalog_category_product')],
                'catalog_category_product.product_id = sales_order_item.product_id'
            )
            ->join(
                ['catalog_category_entity_varchar' => $this->getTable('catalog_category_entity_varchar')],
                'catalog_category_entity_varchar.entity_id = catalog_category_product.category_id'
            )
            ->where('sales_order_item.parent_item_id IS NULL')
            ->where('catalog_category_entity_varchar.attribute_id = 45')
        ;
    }

    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);
        $this->joinCategoryTable($collection);
        $collection->getSelect()
            ->columns([
                'period' => 'catalog_category_entity_varchar.value',
                'total_orders' => 'COUNT(main_table.entity_id)',
                'total_items' => 'SUM(main_table.total_item_count)',
                'subtotal' => 'SUM(main_table.base_subtotal)',
                'tax' => 'SUM(main_table.base_tax_amount)',
                'status' => 'main_table.status',
                'shipping' => 'SUM(main_table.base_shipping_amount)',
                'discounts' => 'SUM(main_table.base_discount_amount)',
                'total' => 'SUM(main_table.base_grand_total)',
                'invoiced' => 'SUM(main_table.base_total_invoiced)',
                'refunded' => 'SUM(main_table.base_total_refunded)',
                'entity_id' => 'CONCAT(main_table.entity_id,catalog_category_product.category_id,\''.$this->createUniqueEntity().'\')'
            ])
        ;
    }

    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
        $this->addGroupBy($collection);
    }

    public function addGroupBy($collection)
    {
        $collection->getSelect()
            ->group("catalog_category_product.category_id")
        ;
    }
}
