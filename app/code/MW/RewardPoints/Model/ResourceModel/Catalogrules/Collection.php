<?php

namespace MW\RewardPoints\Model\ResourceModel\Catalogrules;

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
    		'MW\RewardPoints\Model\Catalogrules',
    		'MW\RewardPoints\Model\ResourceModel\Catalogrules'
    	);
    }
}
