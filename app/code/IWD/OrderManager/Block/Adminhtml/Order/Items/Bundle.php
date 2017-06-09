<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items;

/**
 * Class Bundle
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items
 */
class Bundle extends AbstractType
{
    /**
     * @return int|float
     */
    public function getStockQty()
    {
        $childItems = $this->getOrderItem()->getChildrenItems();
        $simplesQty = [];

        /**
         * @var \IWD\OrderManager\Model\Order\Item $item
         */
        foreach ($childItems as $item) {
            $stock = $this->getStockObjectForOrderItem($item);
            $simplesQty[] = $stock->getQty() + $this->getItemQty($item);
        }

        if (count($simplesQty) == 0) {
            return 1;
        }

        return min($simplesQty);
    }
}
