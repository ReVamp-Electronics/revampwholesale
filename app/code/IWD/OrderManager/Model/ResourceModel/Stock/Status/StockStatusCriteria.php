<?php

namespace IWD\OrderManager\Model\ResourceModel\Stock\Status;

class StockStatusCriteria extends \Magento\CatalogInventory\Model\ResourceModel\Stock\Status\StockStatusCriteria
{
    /**
     * @inheritdoc
     */
    public function setStockFilter($stock)
    {
        $this->data['stock_filter'] = $stock;
    }
}
