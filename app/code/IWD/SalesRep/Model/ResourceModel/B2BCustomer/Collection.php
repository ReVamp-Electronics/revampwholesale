<?php

namespace IWD\SalesRep\Model\ResourceModel\B2BCustomer;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\ResourceModel\B2BCustomer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\B2BCustomer', '\IWD\SalesRep\Model\ResourceModel\B2BCustomer');
    }

    public function translateCondition($field, $condition)
    {
        return $this->_translateCondition($field, $condition);
    }
}
