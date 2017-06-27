<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem;

use \IWD\OrderManager\Model\Quote\Item;
use \IWD\OrderManager\Block\Adminhtml\Order\Items\AbstractType as ItemsAbstract;

/**
 * Class AbstractType
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem
 */
class AbstractType extends ItemsAbstract
{
    /**
     * @param null $orderItem
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockObjectForOrderItem($orderItem = null)
    {
        if ($orderItem == null) {
            $orderItem = $this->getOrderItem();
        }

        $productId = $orderItem->getProductId();
        $storeId = $orderItem->getStoreId();
        if ($orderItem->getProductType() == 'configurable') {
            /** @var Item $childQuoteItem */
            $childQuoteItem = $this->_objectManager
                ->create('\Magento\Quote\Model\Quote\Item')
                ->load($orderItem->getQuoteItemId(), 'parent_item_id');

            if (!empty($childQuoteItem)) {
                $productId = $childQuoteItem->getProductId();
                $storeId = $childQuoteItem->getStoreId();
            }
        }

        $stockItem = $this->stockRegistry->getStockItem(
            $productId,
            $storeId
        );

        return $stockItem;
    }

    /**
     * @param null $orderItem
     * @return float|int
     */
    public function getItemQty($orderItem = null)
    {
        if ($orderItem == null) {
            $orderItem = $this->getOrderItem();
        }

        $itemQty = $orderItem->getQtyOrdered();
        $itemQty = $itemQty < 0 ? 0 : $itemQty;
        $stockQty = $this->getStockQty();

        return $itemQty > $stockQty ? $stockQty : $itemQty;
    }

    /**
     * @return float
     */
    public function getStockQty()
    {
        return $this->getStockObjectForOrderItem()->getQty();
    }

    /**
     * @return string
     */
    public function getPrefixId()
    {
        return Item::PREFIX_ID;
    }

    /**
     * @return string
     */
    public function getEditedItemType()
    {
        return 'quote';
    }
}
