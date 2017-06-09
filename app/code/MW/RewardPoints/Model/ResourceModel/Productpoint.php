<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Productpoint extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_product_point', 'id');
	}
}
