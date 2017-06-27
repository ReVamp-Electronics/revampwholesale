<?php

namespace MW\RewardPoints\Model\ResourceModel;

class Catalogrules extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
	/**
     * Define main table
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('mw_reward_catalog_rules', 'rule_id');
	}
}
