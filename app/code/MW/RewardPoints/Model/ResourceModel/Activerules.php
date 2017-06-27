<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Activerules extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_active_rules', 'rule_id');
	}
}
