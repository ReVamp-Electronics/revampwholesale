<?php

namespace MW\RewardPoints\Controller\Invitation;

class Loginmail extends \MW\RewardPoints\Controller\Invitation
{
	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('My Invitation'));

		return $resultPage;
	}
}
