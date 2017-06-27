<?php

namespace IWD\OrderManager\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status\StockStatusCriteriaMapper as MageStockStatusCriteriaMapper;

/**
 * Class StockStatusCriteriaMapper
 * @package IWD\OrderManager\Model\ResourceModel\Stock\Status
 */
class StockStatusCriteriaMapper extends MageStockStatusCriteriaMapper
{
    /**
     * @inheritdoc
     */
    public function mapStockFilter($stock)
    {
        $this->addFieldToFilter('main_table.stock_id', $stock);
    }
}
