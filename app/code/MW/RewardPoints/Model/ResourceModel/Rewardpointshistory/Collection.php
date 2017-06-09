<?php

namespace MW\RewardPoints\Model\ResourceModel\Rewardpointshistory;

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
    		'MW\RewardPoints\Model\Rewardpointshistory',
    		'MW\RewardPoints\Model\ResourceModel\Rewardpointshistory'
    	);
    }
}
