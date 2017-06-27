<?php

namespace IWD\MultiInventory\Model\ResourceModel\Warehouses;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SourceItem
 * @package IWD\MultiInventory\Model\ResourceModel\Warehouses
 */
class SourceItem extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('iwd_cataloginventory_stock_item', 'item_id');
    }
}
