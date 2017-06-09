<?php

namespace IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceItem
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\MultiInventory\Model\Warehouses\SourceItem',
            'IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceItem'
        );
    }
}
