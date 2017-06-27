<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_point_customer', 'customer_id');
	}

	/**
	 * Insert new member
	 *
	 * @param  array $memberData
	 * @return void
	 */
	public function insertNewMember($memberData)
	{
		$table = $this->getTable('mw_reward_point_customer');
		$data = [
			'customer_id' => $memberData['customer_id'],
			'mw_reward_point' => 0,
			'mw_friend_id' => $memberData['mw_friend_id']
		];

		$this->getConnection()->insert($table, $data);
	}
}
