<?php

namespace MW\RewardPoints\Model\ResourceModel\Rewardpointsorder;

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
    		'MW\RewardPoints\Model\Rewardpointsorder',
    		'MW\RewardPoints\Model\ResourceModel\Rewardpointsorder'
    	);
    }
}
