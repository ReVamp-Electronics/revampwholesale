<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

class Index extends \MW\RewardPoints\Controller\Rewardpoints
{
	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();

		//Check invition information if exist add reward point to friend
        $friendId = $this->_objectManager->get(
        	'Magento\Framework\Stdlib\CookieManagerInterface'
        )->getCookie('friend');
		$customerId = $this->_customerSession->getCustomer()->getId();
		$this->_dataHelper->checkAndInsertCustomerId($customerId, $friendId);

		$resultPage->getConfig()->getTitle()->set(__('My Reward Points'));

		return $resultPage;
	}
}
