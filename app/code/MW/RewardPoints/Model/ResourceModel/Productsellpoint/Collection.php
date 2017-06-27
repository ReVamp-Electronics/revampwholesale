<?php

namespace MW\RewardPoints\Model\ResourceModel\Productsellpoint;

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
    		'MW\RewardPoints\Model\Productsellpoint',
    		'MW\RewardPoints\Model\ResourceModel\Productsellpoint'
    	);
    }
}
