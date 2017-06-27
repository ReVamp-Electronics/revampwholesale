<?php

namespace MW\RewardPoints\Controller\Adminhtml\Member;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Save extends \MW\RewardPoints\Controller\Adminhtml\Member
{
    /**
     * Save transaction for member
     *
     * @return void
     */
    public function execute()
    {
		if ($this->getRequest()->getPost()) {
		    $data = $this->getRequest()->getParams();
			$memberId = $this->getRequest()->getParam('id');
			$session = $this->_objectManager->get('Magento\Backend\Model\Session');

			try {
				if ($memberId != '') {
					$_customer 	= $this->_objectManager->get('MW\RewardPoints\Model\Customer')->load($memberId);
					$oldPoints 	= $_customer->getMwRewardPoint();
					$amount 	= $data['reward_points_amount'];
					$action 	= $data['reward_points_action'];
					$comment 	= $data['reward_points_comment'];
					$newPoints 	= $oldPoints + $amount * $action;

					if ($newPoints < 0) {
						$newPoints = 0;
					}

			    	$amount = abs($newPoints - $oldPoints);
			    	if ($amount > 0) {
			    		$_dataHelper = $this->_objectManager->get('MW\RewardPoints\Helper\Data');
						$_customer->setData('mw_reward_point', $newPoints);
				    	$_customer->save();
				    	$balance = $_customer->getMwRewardPoint();
						$storeId = $this->_objectManager->get('Magento\Customer\Model\Customer')
							->load($_customer->getId())
							->getStoreId();

				    	if ($action > 0) {
				    		$typeOfTransaction = Type::ADMIN_ADDITION;
				    	} else {
				    		$typeOfTransaction = Type::ADMIN_SUBTRACT;
				    	}

				    	// Get expired points information
				    	$results        = $_dataHelper->getTransactionExpiredPoints($amount, $storeId);
                        $expiredDay     = $results[0];
                        $expiredTime    = $results[1];
                        $remainingPoint = $results[2];

				    	$historyData = [
				    		'type_of_transaction' => $typeOfTransaction,
							'amount' => $amount,
							'balance' => $balance,
							'transaction_detail' => $comment,
							'transaction_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
							'expired_day' => $expiredDay,
							'expired_time' => $expiredTime,
							'point_remaining' => $remainingPoint,
							'status' => Status::COMPLETE
						];
				    	$_customer->saveTransactionHistory($historyData);

				    	// Process expired points when spent point
                        if ($action < 0) {
                            $_dataHelper->processExpiredPointsWhenSpentPoints($_customer->getId(), $amount);
                        }

				    	// Send mail when points are changed
						$store = $this->_objectManager->get('Magento\Store\Model\Store')->load($storeId);
						$_dataHelper->sendEmailCustomerPointChanged(
							$_customer->getId(),
							$historyData,
							$store->getCode()
						);
			    	}
				}

				$this->messageManager->addSuccess(__('The member has successfully saved'));
				$session->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('Unable to find member to save'));
        $this->_redirect('*/*/');
    }
}
