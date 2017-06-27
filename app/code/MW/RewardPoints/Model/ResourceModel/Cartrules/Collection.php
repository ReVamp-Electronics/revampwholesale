<?php

namespace MW\RewardPoints\Model\ResourceModel\Cartrules;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
	/**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
    	$this->_init(
    		'MW\RewardPoints\Model\Cartrules',
    		'MW\RewardPoints\Model\ResourceModel\Cartrules'
    	);
    }
}
