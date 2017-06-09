<?php

namespace MW\RewardPoints\Controller\Adminhtml\Spendcartrules;

class NewAction extends \MW\RewardPoints\Controller\Adminhtml\Spendcartrules
{
	/**
     * Create new shopping cart spending rule action
     *
     * @return void
     */
	public function execute()
	{
		$this->_forward('edit');
	}
}
