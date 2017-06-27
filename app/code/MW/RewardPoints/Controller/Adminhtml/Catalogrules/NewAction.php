<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

class NewAction extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
	/**
     * Create new catalog reward rule action
     *
     * @return void
     */
	public function execute()
	{
		$this->_forward('edit');
	}
}
