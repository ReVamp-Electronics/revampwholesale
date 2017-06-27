<?php

namespace IWD\SalesRep\Model\ResourceModel\Order;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\ResourceModel\Order
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var bool
     */
    protected $_isTotals = false;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\Order', '\IWD\SalesRep\Model\ResourceModel\Order');
    }
}
