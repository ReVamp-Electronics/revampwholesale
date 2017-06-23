<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\ByAttributes;

use Amasty\Reports\Traits\Filters;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
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

    protected $_idFieldName = '';

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
    
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);
        $this->joinEavAttribute($collection);
        $collection->getSelect()
            ->columns([
                'value' => 'IF(eaov1.value IS NULL, eaov2.value, eaov1.value)',
                'total_orders' => 'IF(main_table.qty_ordered = 0, COUNT(soi.qty_ordered), COUNT(main_table.qty_ordered))',
                'qty' => 'IF(main_table.qty_ordered = 0, COUNT(soi.qty_ordered), COUNT(main_table.qty_ordered))',
                'total' => 'IF(main_table.base_price = 0, SUM(soi.base_price), SUM(main_table.base_price))',
                'tax' => 'IF(main_table.base_tax_amount = 0, SUM(soi.base_tax_amount), SUM(main_table.base_tax_amount))',
                'discounts' => 'IF(main_table.base_discount_amount = 0, SUM(soi.base_discount_amount), SUM(main_table.base_discount_amount))',
                'invoiced' => 'IF(main_table.base_row_invoiced = 0, SUM(soi.base_row_invoiced), SUM(main_table.base_row_invoiced))',
                'refunded' => 'IF(main_table.base_amount_refunded = 0, SUM(soi.base_amount_refunded), SUM(main_table.base_amount_refunded))',
                'entity_id' => 'CONCAT(eaov1.value,eaov2.value,\''.$this->createUniqueEntity().'\')'
            ])
        ;
    }

    public function joinEavAttribute($collection)
    {
        $filters = $this->request->getParam('amreports');
        $eav = isset($filters['eav']) ? $filters['eav'] : 'name';
        $collection->getSelect()
            ->joinLeft(
                ['soi' => $this->getTable('sales_order_item')],
                'soi.item_id = main_table.parent_item_id'
            )

            ->joinLeft(
                ['cpei1' => $this->getTable('catalog_product_index_eav')],
                'main_table.product_id = cpei1.entity_id AND main_table.store_id = cpei1.store_id'
            )
            ->joinLeft(
                ['cpei2' => $this->getTable('catalog_product_index_eav')],
                'main_table.product_id = cpei2.entity_id AND cpei2.store_id = 0'
            )

            ->joinLeft(
                ['eaov1' => $this->getTable('eav_attribute_option_value')],
                'cpei1.value = eaov1.option_id'
            )
            ->joinLeft(
                ['eaov2' => $this->getTable('eav_attribute_option_value')],
                'cpei2.value = eaov2.option_id'
            )
            ->where('cpei2.attribute_id = ? OR cpei1.attribute_id = ?', $eav)
            ->group("cpei1.value")
            ->group("cpei2.value");
    }

    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
    }
    
    public function addItem(\Magento\Framework\DataObject $item)
    {
        parent::_addItem($item); // TODO: Change the autogenerated stub
        return $this;
    }
}
