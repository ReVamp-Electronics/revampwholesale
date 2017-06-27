<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Spendcartrules extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_spend_cart_rules', 'rule_id');
	}
}
