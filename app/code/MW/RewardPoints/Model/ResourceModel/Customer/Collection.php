<?php

namespace MW\RewardPoints\Model\ResourceModel\Customer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
    	$this->_init(
    		'MW\RewardPoints\Model\Customer',
    		'MW\RewardPoints\Model\ResourceModel\Customer'
    	);
    }
}
