<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Rewardpointsorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_point_order', 'order_id');
	}

	/**
	 * Save reward points information after save order
	 *
	 * @param  array $orderData
	 * @return void
	 */
	public function saveRewardOrder($orderData)
	{
        $table = $this->getTable('mw_reward_point_order');

		$this->getConnection()->insert($table, $orderData);
	}
}
