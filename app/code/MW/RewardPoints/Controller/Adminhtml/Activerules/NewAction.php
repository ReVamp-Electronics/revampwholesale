<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

class NewAction extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
	/**
     * Create new customer behavior rule action
     *
     * @return void
     */
	public function execute()
	{
		$this->_forward('edit');
	}
}
