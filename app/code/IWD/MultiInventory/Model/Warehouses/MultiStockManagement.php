<?php

namespace IWD\MultiInventory\Model\Warehouses;

use Magento\Framework\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use IWD\MultiInventory\Ui\Component\Listing\Column\Stock\Options as ColumnStockOptions;
use IWD\MultiInventory\Api\MultiStockManagementInterface;
use IWD\MultiInventory\Api\SourceRepositoryInterface;
use IWD\MultiInventory\Api\SourceItemRepositoryInterface;

/**
 * Class MultiStockManagement
 * @package IWD\MultiInventory\Model\Warehouses
 */
class MultiStockManagement implements MultiStockManagementInterface
{
    /**
     * @var OrderInterface
     */
    private $currentOrder = null;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var []
     */
    private $stockItemsList = [];

    /**
     * @var StockOrderItem
     */
    private $stockOrderItem;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * MultiStockManagement constructor.
     * @param UrlInterface $urlBuilder
     * @param StockOrderItem $stockOrderItem
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StockOrderItem $stockOrderItem,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        SourceItemRepositoryInterface $sourceItemRepository,
        OrderRepositoryInterface $orderRepository,
        StockRegistryInterface $stockRegistry,
        ProductRepositoryInterface $productRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->stockOrderItem = $stockOrderItem;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->orderRepository = $orderRepository;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function loadOrder($orderId)
    {
        $this->currentOrder = $this->orderRepository->get($orderId);
        return $this;
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        return $this->currentOrder;
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->currentOrder = $order;
        return $this;
    }

    /**
     * Get qty ordered for order
     *
     * @return float
     */
    public function getOrderQtyOrdered()
    {
        $items = $this->getOrder()->getItems();
        $qty = 0;
        foreach ($items as $item) {
            if (!$item->isDeleted() && !$item->getIsVirtual() && !$item->getHasChildren()) {
                $qty += $item->getQtyOrdered() - $item->getQtyCanceled() - $item->getQtyRefunded();
            }
        }
        return $qty;
    }

    /**
     * Get qty refunded for order
     *
     * @return float
     */
    public function getOrderRefundedQty()
    {
        $orderId = $this->getOrder()->getEntityId();
        $stockOrderItems = $this->stockOrderItem->getStockOrderItems($orderId, true);

        $items = $this->getOrder()->getItems();
        $qtyRefunded = 0;
        foreach ($items as $item) {
            if (!$item->isDeleted() && !$item->getIsVirtual() && !$item->getHasChildren()) {
                $ordered = $item->getQtyOrdered() - $item->getQtyCanceled() - $item->getQtyRefunded();
                $assigned = isset($stockOrderItems[$item->getItemId()]['assignedQty'])
                    ? $stockOrderItems[$item->getItemId()]['assignedQty']
                    : 0;

                $qtyRefunded += ($ordered - $assigned) < 0 ? ($ordered - $assigned) : 0;
            }
        }

        return $qtyRefunded;
    }

    /**
     * Get qty assigned for order
     *
     * @return float
     */
    public function getOrderQtyAssigned()
    {
        return $this->getOrder()->getIwdQtyAssigned();
    }

    /**
     * Get is order assigned to stock
     *
     * @return bool
     */
    public function getIsOrderAssignedToStock()
    {
        return $this->getOrder()->getIwdStockAssigned() == ColumnStockOptions::ASSIGNED;
    }

    /**
     * Get is order stock not applicable
     *
     * @return bool
     */
    public function getIsOrderStockNotApplicable()
    {
        return $this->getOrder()->getIwdStockAssigned() == ColumnStockOptions::NOT_APPLICABLE;
    }

    /**
     * Get is order placed before init multi stock inventory
     *
     * @return bool
     */
    public function getIsOrderPlacedBeforeInit()
    {
        return $this->getOrder()->getIwdStockAssigned() == ColumnStockOptions::ORDER_PLACED_BEFORE;
    }

    /**
     * Get stocks list
     *
     * @return array
     */
    public function getStocksList()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $stocks = $this->sourceRepository->getList($searchCriteria)->getItems();

        $stocksList = [];

        foreach ($stocks as $stock) {
            $stocksList[$stock->getStockId()] = [
                'id' => $stock->getStockId(),
                'stockName' => $stock->getStockName(),
                'stockUrl' => $this->urlBuilder->getUrl(
                    'iwdmultiinventory/warehouses/edit',
                    ['id' => $stock->getStockId()]
                ),
            ];
        }

        return $stocksList;
    }

    /**
     * Get order items
     *
     * @return array
     */
    public function getOrderItems()
    {
        $orderItems = $this->getOrder()->getItems();
        $items = [];

        foreach ($orderItems as $item) {
            if ($item->isDeleted() || $item->getIsVirtual() || $item->getParentItemId()) {
                continue;
            }

            $items[$item->getItemId()] = $this->prepareOrderItemArray($item);

            if ($this->isComplexProduct($item)) {
                $childItems = [];
                foreach ($orderItems as $i) {
                    if ($i->getParentItemId() == $item->getItemId()) {
                        $childItems[$i->getItemId()] = $this->prepareOrderItemArray($i);
                    }
                }
                $items[$item->getItemId()]['childItems'] = $childItems;
            }
        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return array
     */
    private function prepareOrderItemArray($item)
    {
        return [
            'orderItemId' => $item->getItemId(),
            'productId' => $item->getProductId(),
            'productName' => $item->getName(),
            'productUrl' => $this->urlBuilder->getUrl(
                'catalog/product/edit',
                ['id' => $item->getProductId(), 'active_tab' => 'advanced-inventory']
            ),
            'qtyOrdered' => ($item->getQtyOrdered() - $item->getQtyRefunded() - $item->getQtyCanceled()) * 1,
            'stockItems' => $this->getStockItemsForOrderItem($item),
            'hasChildItems' => $this->isComplexProduct($item),
        ];
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return bool
     */
    private function isComplexProduct($item)
    {
        return $item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            || $item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return array
     */
    private function getStockItemsForOrderItem($orderItem)
    {
        $productId = $orderItem->getProductId();
        $orderItemId = $orderItem->getId();
        $orderId = $orderItem->getOrderId();

        $stockItemsList = $this->prepareStockItemsList();
        $stockItems = $this->stockOrderItem->getStockItemsForProduct($productId);
        $stockItemsForOrder = $this->stockOrderItem->getStockItemsForOrder($orderId);

        foreach ($stockItems as $stock) {
            $stockId = $stock['stock_id'];
            if (isset($stockItemsList[$stockId])) {
                $assignedQty = isset($stockItemsForOrder[$stockId][$orderItemId]['qty_stock_assigned'])
                    ? $stockItemsForOrder[$stockId][$orderItemId]['qty_stock_assigned']
                    : 0;
                $stockItemsList[$stockId]['allowedQty'] = $stock['qty'] * 1;
                $stockItemsList[$stockId]['assignedQty'] = $assignedQty * 1;
                $stockItemsList[$stockId]['orderItemId'] = $orderItemId;
                $stockItemsList[$stockId]['productId'] = $productId;
                $stockItemsList[$stockId]['isInStock'] = (int)$stock['is_in_stock'];
            }
        }
        return $stockItemsList;
    }

    /**
     * @return array
     */
    private function prepareStockItemsList()
    {
        if (empty($this->stockItemsList)) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $stocks = $this->sourceRepository->getList($searchCriteria)->getItems();
            $this->stockItemsList = [];
            foreach ($stocks as $stock) {
                $this->stockItemsList[$stock->getStockId()] = [
                    'stockId' => $stock->getStockId(),
                    'allowedQty' => 0,
                    'assignedQty' => 0,
                ];
            }
        }

        return $this->stockItemsList;
    }

    /**
     * @param $stockItems
     */
    public function updateStockItems($stockItems)
    {
        foreach ($stockItems as $productId => $stocks) {
            foreach ($stocks as $stockId => $params) {
                if ($stockId == \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID) {
                    $this->updateDefaultStock($productId, $params);
                } else {
                    $this->updateAdditionalStock($productId, $stockId, $params);
                }
            }
        }
    }

    /**
     * @param $productId
     * @param $params
     */
    private function updateDefaultStock($productId, $params)
    {
        $item = $this->stockRegistry->getStockItem($productId);

        if (is_array($params)) {
            $qty = isset($params['qty']) ? $params['qty'] : 0;
            $isInStock = isset($params['is_in_stock']) ? $params['is_in_stock'] : 0;
            $item->setQty($qty)->setIsInStock($isInStock);
        } else {
            $item->setQty($params);
        }

        $sku = $this->productRepository->getById($productId)->getSku();
        $this->stockRegistry->updateStockItemBySku($sku, $item);
    }

    /**
     * @param $productId
     * @param $stockId
     * @param $params
     */
    private function updateAdditionalStock($productId, $stockId, $params)
    {
        $item = $this->sourceItemRepository->getItem($productId, $stockId);

        if (is_array($params)) {
            if (isset($params['qty'])) {
                $item->setQty($params['qty']);
            }
            if (isset($params['is_in_stock'])) {
                $item->setIsInStock($params['is_in_stock']);
            }
        } else {
            $item->setQty($params);
        }

        $item->getItemId();

        $this->sourceItemRepository->save($item);
    }

    /**
     * @param $orderItems
     */
    public function updateOrderItems($orderItems)
    {
        $orderId = $this->getOrder()->getEntityId();

        foreach ($orderItems as $orderItemId => $stocks) {
            foreach ($stocks as $stockId => $qty) {
                $this->stockOrderItem
                    ->setOrderId($orderId)
                    ->setOrderItemId($orderItemId)
                    ->setStockId($stockId)
                    ->setQtyStockAssigned($qty)
                    ->save();
            }
        }
    }

    /**
     * @return float
     */
    public function updateOrderAssignedQty()
    {
        $orderId = $this->getOrder()->getEntityId();

        $iwdQtyAssigned = $this->stockOrderItem->getOrderAssignedQty($orderId);
        $iwdStockAssigned = $this->getIwdStockAssigned($iwdQtyAssigned);

        $this->getOrder()
            ->setData('iwd_qty_assigned', $iwdQtyAssigned)
            ->setData('iwd_stock_assigned', $iwdStockAssigned)
            ->save();

        return $iwdQtyAssigned;
    }

    /**
     * @param $iwdQtyAssigned
     * @return int
     */
    private function getIwdStockAssigned($iwdQtyAssigned)
    {
        $qtyOrdered = $this->getOrderQtyOrdered();

        if ($qtyOrdered == $iwdQtyAssigned) {
            if ($qtyOrdered == 0) {
                return ColumnStockOptions::NOT_APPLICABLE;
            }
            return ColumnStockOptions::ASSIGNED;
        }

        return ColumnStockOptions::NOT_ASSIGNED;
    }

    /**
     * @return void
     */
    public function autoSyncStocks()
    {
        if ($this->isOrderPlacedBefore()) {
            return;
        }

        $order = $this->getOrder();
        $orderId = $order->getEntityId();
        $orderItems = $this->stockOrderItem->getOrderItemsWithAssignedStocks($orderId);
        foreach ($orderItems as $row) {
            $assigned = $row['qty_stock_assigned'];
            $ordered = $row['qty_ordered'];
            $stocksCount = $row['stocks_count'];

            if ($assigned != $ordered) {
                if ($stocksCount > 1) {
                    continue;
                }
                $stockId = $row['stock_id'];
                $orderItemId = $row['item_id'];

                $item = $this->sourceItemRepository->getItem($row['product_id'], $row['stock_id']);
                $qty = $item->getQty() + ($assigned - $ordered);
                $item->setQty($qty);
                $this->sourceItemRepository->save($item);

                $this->stockOrderItem->updateStockOrderItemQty($stockId, $orderItemId, $ordered);
            }
        }

        $assigned = $this->stockOrderItem->getOrderAssignedQty($orderId);
        $order->setIwdQtyAssigned($assigned);
        $order->save()->save();
    }

    /**
     * @return bool
     */
    public function needUpdateStocks()
    {
        if ($this->isOrderPlacedBefore()) {
            return false;
        }
        return !$this->getIsOrderAssignedToStock();
    }

    /**
     * @return bool
     */
    private function isOrderPlacedBefore()
    {
        $orderId = $this->getOrder()->getEntityId();
        return $this->stockOrderItem->getIsOrderPlacedBeforeInit($orderId);
    }

    /**
     * @param $orderItemId
     * @param $productId
     */
    public function backToInventory($orderItemId, $productId)
    {
        $stockItems = $this->stockOrderItem->getStockItemsForOrderItem($orderItemId);

        foreach ($stockItems as $item) {
            $stockItem = $this->sourceItemRepository->getItem($productId, $item['stock_id']);
            $qty = $stockItem->getQty() + $item['qty_stock_assigned'];
            $stockItem->setQty($qty);
            $this->sourceItemRepository->save($stockItem);

            $this->stockOrderItem->getStockId($item['stock_id']);
            $this->stockOrderItem->getOrderItemId($orderItemId);
            $this->stockOrderItem->removeStockOrderItem();
        }
    }
}
