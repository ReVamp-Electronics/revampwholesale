<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Setpointsfblike extends \MW\RewardPoints\Controller\Rewardpoints
{
	public function execute()
	{
		$customer = $this->_customerSession->getCustomer();
		if ($customer) {
			$customerId = $customer->getId();
			$pageUrl = trim($this->getRequest()->getParam('pageurl'));
			$dataHelper = $this->_objectManager->get('MW\RewardPoints\Helper\Data');
			$likeUrl = $dataHelper->getLinkShareFacebook($pageUrl);

			// Get store of customer
			$store = $this->_objectManager->get(
				'Magento\Store\Model\Store'
			)->load($customer->getStoreId());

			// Get customer group ID
			$customerGroupId = $dataHelper->getCustomerGroupIdFrontend();

			// Get active rules
			$results = $this->_objectManager->get(
				'MW\RewardPoints\Model\Activerules'
			)->getResultActiveRulesExpiredPoints(
				Type::LIKE_FACEBOOK,
				$customerGroupId,
				$store->getId()
			);

			if ($dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $results[0])) {
				$points = $results[0];

				if ($points > 0) {
					$expiredDay      = $results[1];
					$expiredTime     = $results[2];
					$remainingPoints = $results[3];
					$transactions = $this->_objectManager->get(
						'MW\RewardPoints\Model\Rewardpointshistory'
					)->getCollection()
					->addFieldToFilter('customer_id', $customerId)
					->addFieldToFilter('type_of_transaction', Type::LIKE_FACEBOOK)
					->addFieldToFilter('transaction_detail', $likeUrl)
					->addFieldToFilter('status', ['eq' => Status::COMPLETE]);

					if (sizeof($transactions) == 0) {
						$this->_dataHelper->checkAndInsertCustomerId($customerId, 0);
						$_customer = $this->_objectManager->get(
							'MW\RewardPoints\Model\Customer'
						)->load($customerId);
						$_customer->addRewardPoint($points);

						// Save transaction history
						$historyData = [
							'type_of_transaction' => Type::LIKE_FACEBOOK,
							'amount' => (int) $points,
							'balance' => $_customer->getMwRewardPoint(),
							'transaction_detail' => $likeUrl,
							'transaction_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
							'expired_day' => $expiredDay,
							'expired_time' => $expiredTime,
							'point_remaining' => $remainingPoints,
							'status' => Status::COMPLETE
						];
						$_customer->saveTransactionHistory($historyData);

						// Send mail when points changed
						$this->_dataHelper->sendEmailCustomerPointChanged(
							$_customer->getId(),
							$historyData,
							$store->getCode()
						);
					}
				}
			}
		}
	}
}
