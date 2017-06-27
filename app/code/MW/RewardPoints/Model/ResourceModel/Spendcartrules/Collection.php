<?php

namespace MW\RewardPoints\Model\ResourceModel\Spendcartrules;

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
    		'MW\RewardPoints\Model\Spendcartrules',
    		'MW\RewardPoints\Model\ResourceModel\Spendcartrules'
    	);
    }
}
