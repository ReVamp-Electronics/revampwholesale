<?php

namespace IWD\SalesRep\Model\ResourceModel\Customer;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\ResourceModel\Customer
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
        $this->_init('\IWD\SalesRep\Model\Customer', '\IWD\SalesRep\Model\ResourceModel\Customer');
    }

    public function addSalesRepFilter($salesrepId)
    {
        return $this->addFieldToFilter('salesrep_filter', $salesrepId);
    }
}
