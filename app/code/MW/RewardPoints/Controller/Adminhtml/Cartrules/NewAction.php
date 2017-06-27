<?php

namespace MW\RewardPoints\Controller\Adminhtml\Cartrules;

class NewAction extends \MW\RewardPoints\Controller\Adminhtml\Cartrules
{
	/**
     * Create new shopping cart reward rule action
     *
     * @return void
     */
	public function execute()
	{
		$this->_forward('edit');
	}
}
