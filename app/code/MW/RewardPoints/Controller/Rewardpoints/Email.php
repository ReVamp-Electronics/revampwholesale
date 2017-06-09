<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

class Email extends \MW\RewardPoints\Controller\Rewardpoints
{
	public function execute()
	{
		$subscribedBalanceUpdate = $this->getRequest()->getPost('subscribed_balance_update');
		if ($subscribedBalanceUpdate == null) {
			$subscribedBalanceUpdate = 0;
		}

		$subscribedPointExpiration = $this->getRequest()->getPost('subscribed_point_expiration');
		if ($subscribedPointExpiration == null) {
			$subscribedPointExpiration = 0;
		}

		$member = $this->_objectManager->get('MW\RewardPoints\Model\Customer');
		$customer = $member->load($this->_customerSession->getCustomer()->getId());
		$customer->setSubscribedBalanceUpdate($subscribedBalanceUpdate)
			->setSubscribedPointExpiration($subscribedPointExpiration)
			->save();

		$this->messageManager->addSuccess(__('The Email Notification has been saved.'));

		return $this->resultRedirectFactory->create()->setPath('rewardpoints/rewardpoints/index');
	}
}
