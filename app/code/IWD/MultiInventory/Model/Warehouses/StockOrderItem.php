<?php

namespace IWD\MultiInventory\Model\Warehouses;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use IWD\MultiInventory\Ui\Component\Listing\Column\Stock\Options as ColumnStockOptions;

/**
 * Class StockOrderItem
 * @package IWD\MultiInventory\Model\Warehouses
 */
class StockOrderItem extends AbstractModel
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $stockId;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var int
     */
    private $orderItemId;

    /**
     * @var float
     */
    private $qtyStockAssigned;

    /**
     * @var []
     */
    private $stockItemsForOrder;

    /**
     * @var []
     */
    private $stockItemsForOrderItem;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * StockOrderItem constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->stockItemsForOrder = [];
        $this->stockItemsForOrderItem = [];
    }

    /**
     * @return int
     */
    public function getStockId()
    {
        return $this->stockId;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * @return float
     */
    public function getQtyStockAssigned()
    {
        return $this->qtyStockAssigned;
    }

    /**
     * @param $stockId
     * @return $this
     */
    public function setStockId($stockId)
    {
        $this->stockId = $stockId;
        return $this;
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param $orderItemId
     * @return $this
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;
        return $this;
    }

    /**
     * @param $qtyStockAssign
     * @return $this
     */
    public function setQtyStockAssigned($qtyStockAssign)
    {
        $this->qtyStockAssigned = $qtyStockAssign;
        return $this;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @return void
     */
    public function removeStockOrderItem()
    {
        $connection = $this->getConnection();

        $table = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');
        $where = $connection->quoteInto('stock_id = ?', $this->getStockId())
            . ' AND '
            . $connection->quoteInto('order_item_id = ? ', $this->getOrderItemId());

        $connection->delete($table, $where);
    }

    /**
     * @return void
     */
    public function addStockOrderItem()
    {
        $connection = $this->getConnection();

        $connection->insert(
            $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item'),
            [
                'stock_id' => $this->getStockId(),
                'order_id' => $this->getOrderId(),
                'order_item_id' => $this->getOrderItemId(),
                'qty_stock_assigned' => $this->getQtyStockAssigned()
            ]
        );
    }

    /**
     * @param $stockId
     * @param $orderItemId
     * @param $qty
     */
    public function updateStockOrderItemQty($stockId, $orderItemId, $qty)
    {
        $connection = $this->getConnection();

        $table = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');
        $where = ['stock_id = ?' => $stockId, 'order_item_id = ?' => $orderItemId];

        $connection->update($table, ['qty_stock_assigned' => $qty], $where);
    }

    /**
     * @return void
     */
    public function save()
    {
        $this->removeStockOrderItem();
        $this->addStockOrderItem();
    }

    /**
     * @param $productId
     * @return array
     */
    public function getStockItemsForProduct($productId)
    {
        $stockItemTable = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_item');

        $select = $this->getConnection()->select()
            ->from(
                ['stock_item' => $stockItemTable],
                ['qty', 'item_id', 'stock_id', 'is_in_stock']
            )
            ->where('product_id=?', $productId);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getStockItemsForOrder($orderId)
    {
        if (empty($this->stockItemsForOrder)) {
            $stockOrderItemTable = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');

            $select = $this->getConnection()->select()
                ->from(
                    ['stock_order_item' => $stockOrderItemTable],
                    ['qty_stock_assigned', 'order_id', 'stock_id', 'order_item_id']
                )
                ->where('order_id=?', $orderId);

            $this->stockItemsForOrder = [];
            $stockItemsForOrder = $this->getConnection()->fetchAll($select);

            foreach ($stockItemsForOrder as $item) {
                $this->stockItemsForOrder[$item['stock_id']][$item['order_item_id']] = $item;
            }
        }

        return $this->stockItemsForOrder;
    }

    /**
     * @param $orderItemId
     * @return array
     */
    public function getStockItemsForOrderItem($orderItemId)
    {
        $stockOrderItemTable = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');

        $select = $this->getConnection()->select()
            ->from(
                ['stock_order_item' => $stockOrderItemTable],
                ['qty_stock_assigned', 'order_id', 'stock_id', 'order_item_id']
            )
            ->where('order_item_id=?', $orderItemId);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param $orderId
     * @return int
     */
    public function getOrderAssignedQty($orderId)
    {
        $stockOrderItemTable = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');

        $select = $this->getConnection()->select()
            ->from(
                ['stock_order_item' => $stockOrderItemTable], ['order_item_id']
            )->columns(
                ['qty_stock_assigned' => new \Zend_Db_Expr('SUM(`qty_stock_assigned`)')]
            )->where(
                'order_id=?', $orderId
            );

        $row = $this->getConnection()->fetchRow($select);

        return (isset($row['qty_stock_assigned']) && !empty($row['qty_stock_assigned']))
            ? $row['qty_stock_assigned'] : 0;
    }

    /**
     * @param $orderId
     * @param bool $reload
     * @return array
     */
    public function getStockOrderItems($orderId, $reload = false)
    {
        if (empty($this->stockItemsForOrderItem) || $reload) {
            $isOrderPlacedBeforeInit = $this->getIsOrderPlacedBeforeInit($orderId);

            $stockOrderItemTable = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');
            $salesOrderItemTable = $this->resourceConnection->getTableName('sales_order_item');

            $select = $this->getConnection()->select()
                ->from(
                    ['sales_order_item' => $salesOrderItemTable],
                    ['order_id', 'item_id', 'is_virtual', 'product_type']
                )->joinLeft(
                    ["stock_order_item" => $stockOrderItemTable],
                    "sales_order_item.item_id = stock_order_item.order_item_id",
                    []
                )->columns(
                    [
                        'qty_ordered' => new \Zend_Db_Expr('(sales_order_item.qty_ordered - sales_order_item.qty_canceled - sales_order_item.qty_refunded)'),
                        'qty_stock_assigned' => new \Zend_Db_Expr('SUM(stock_order_item.qty_stock_assigned)')
                    ]
                )
                ->where('sales_order_item.order_id=?', $orderId)
                ->group('sales_order_item.item_id');

            $rows = $this->getConnection()->fetchAll($select);
            foreach ($rows as $row) {
                $this->stockItemsForOrderItem[$row['item_id']] = [
                    'assignedQty' => $row['qty_stock_assigned'],
                    'orderedQty' => $row['qty_ordered'],
                    'isOrderPlacesBefore' => $isOrderPlacedBeforeInit,
                    'isNotApplicable' => ($row['qty_ordered'] == 0 && $row['qty_stock_assigned'] == 0) || $row['is_virtual'] == 1,
                    'id' => $row['item_id']
                ];
            }
        }

        return $this->stockItemsForOrderItem;
    }

    /**
     * @param $orderId
     * @return bool
     */
    protected function getIsOrderPlacedBeforeInit($orderId)
    {
        return $this->loadOrder($orderId)->getIwdStockAssigned() == ColumnStockOptions::ORDER_PLACED_BEFORE;
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function loadOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getOrderItemsWithAssignedStocks($orderId)
    {
        $stockOrderItemTable = $this->resourceConnection->getTableName('iwd_cataloginventory_stock_order_item');
        $salesOrderItemTable = $this->resourceConnection->getTableName('sales_order_item');

        $select = $this->getConnection()->select()
            ->from(
                ['sales_order_item' => $salesOrderItemTable],
                ['order_id', 'item_id', 'is_virtual', 'product_id']
            )->joinLeft(
                ["stock_order_item" => $stockOrderItemTable],
                "sales_order_item.item_id = stock_order_item.order_item_id",
                ['stock_id']
            )->columns(
                [
                    'stocks_count' => new \Zend_Db_Expr('COUNT(item_id)'),
                    'qty_ordered' => new \Zend_Db_Expr('(sales_order_item.qty_ordered - sales_order_item.qty_canceled - sales_order_item.qty_refunded)'),
                    'qty_stock_assigned' => 'stock_order_item.qty_stock_assigned'
                ]
            )
            ->where('sales_order_item.order_id=?', $orderId)
            ->where('sales_order_item.is_virtual=?', 0)
            ->where('stock_order_item.qty_stock_assigned!=?', 0)
            ->group('sales_order_item.item_id');

        return $this->getConnection()->fetchAll($select);
    }
}
