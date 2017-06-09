<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Exchange extends \MW\RewardPoints\Controller\Rewardpoints
{
    public function execute()
    {
        if (!$this->_dataHelper->allowExchangePointToCredit()) {
            $this->_forward('noroute');
            return;
        }

        $points     = $this->getRequest()->getParam('exchange_points');
        $customerId = $this->_customerSession->getCustomer()->getId();
        $_customer  = $this->_objectManager->get(
            'MW\RewardPoints\Model\Customer'
        )->load($customerId);
        if ($points > $_customer->getRewardPoint()) {
            $this->messageManager->addError(__('You do not enought points to exchange'));
            return;
        }

        if ($this->_dataHelper->getCreditModule()) {
            $exchangeRate = explode('/', $this->_dataHelper->pointCreditRate());
            if (sizeof($exchangeRate) == 2) {
                $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
                if ($points < 0) {
                    $points = -$points;
                }

                $credit = ($points * $exchangeRate[1] * 1.0) / $exchangeRate[0];
                // Add credit to customer
                $customerCredit = $this->_objectManager->get(
                    'MW\Credit\Model\Creditcustomer'
                )->load($customerId);
                $oldCredit      = $customerCredit->getCredit();
                $newCredit      = $oldCredit + $credit;
                $customerCredit->setCredit($newCredit)->save();

                $historyData = [
                    'type_transaction'      => \MW\Credit\Model\TransactionType::SEND_TO_FRIEND,
                    'transaction_detail'    => $points,
                    'amount'                => $credit,
                    'beginning_transaction' => $oldCredit,
                    'end_transaction'       => $newCredit,
                    'created_time'          => $now
                ];
                $this->_objectManager->get(
                    'MW\Credit\Model\Credithistory'
                )->saveTransactionHistory($historyData);

                // Subtract points
                $_customer->addRewardPoint(-$points);
                $historyData = [
                    'type_of_transaction' => Type::EXCHANGE_TO_CREDIT,
                    'amount'              => $points,
                    'balance'             => $_customer->getMwRewardPoint(),
                    'transaction_detail'  => $credit,
                    'transaction_time'    => $now,
                    'status'              => Status::COMPLETE
                ];
                $_customer->saveTransactionHistory($historyData);
                $this->messageManager->addSuccess(__('Your reward points was exchanged to credit successfully'));
            } else {
                $this->messageManager->addError(__('There is a system error. Please contact to administrator.'));
            }
        } else {
            $this->messageManager->addError(__('Credit module error or has not been installed yet'));
        }

        $this->_redirect('rewardpoints/rewardpoints/index');
    }
}
