<?php

namespace IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceAddress;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceAddress
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\MultiInventory\Model\Warehouses\SourceAddress',
            'IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceAddress'
        );
    }
}
