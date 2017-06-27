<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem;

/**
 * Class Bundle
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem
 */
class Bundle extends AbstractType
{
    /**
     * @return int
     */
    public function getStockQty()
    {
        $childItems = $this->getOrderItem()->getChildrenItems();
        $simplesQty = [];

        /**
         * @var \IWD\OrderManager\Model\Quote\Item $item
         */
        foreach ($childItems as $item) {
            $stock = $this->getStockObjectForOrderItem($item);
            $simplesQty[] = $stock->getQty();
        }

        if (count($simplesQty) == 0) {
            return 1;
        }

        return min($simplesQty);
    }
}
