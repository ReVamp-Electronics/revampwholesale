<?php

namespace IWD\MultiInventory\Model\ResourceModel\Warehouses;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SourceAddress
 * @package IWD\MultiInventory\Model\ResourceModel\Warehouses
 */
class SourceAddress extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('iwd_cataloginventory_stock_address', 'id');
    }
}
