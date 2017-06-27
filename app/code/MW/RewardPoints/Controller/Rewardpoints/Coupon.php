<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Coupon extends \MW\RewardPoints\Controller\Rewardpoints
{
    public function execute()
    {
        $check           = 0;
        $couponCode      = trim($this->getRequest()->getParam('coupon_code'));
        $activeRuleModel = $this->_objectManager->get('MW\RewardPoints\Model\Activerules');
        $ruleId          = $activeRuleModel->getRuleIdbyCouponCode($couponCode);
        $customer        = $this->_customerSession->getCustomer();
        $customerId      = $customer->getId();

        if ($customerId && $ruleId) {
            $transactions = $this->_objectManager->get(
                'MW\RewardPoints\Model\Rewardpointshistory'
            )->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('type_of_transaction', Type::CUSTOM_RULE)
            ->addFieldToFilter('transaction_detail', $ruleId)
            ->addFieldToFilter('status', Status::COMPLETE);

            if (!sizeof($transactions)) {
                $dataHelper = $this->_objectManager->get('MW\RewardPoints\Helper\Data');
                $dataHelper->checkAndInsertCustomerId($customerId, 0);

                // Get rewardpoint customer information
                $_customer = $this->_objectManager->get(
                    'MW\RewardPoints\Model\Customer'
                )->load($customerId);

                // Get customer group ID
                $customerGroupId = $this->_objectManager->get(
                    'Magento\Customer\Model\Customer'
                )->load($customerId)->getGroupId();

                $storeId = $customer->getStoreId();
                $results = $activeRuleModel->getPointByRuleId($ruleId, $customerGroupId, $storeId);
                $points  = $results[0];

                if ($points > 0) {
                    $expiredDay      = $results[1];
                    $expiredTime     = $results[2];
                    $remainingPoints = $results[3];
                    $check = 1;
                    $_customer->addRewardPoint($points);
                    $historyData = [
                        'type_of_transaction' => Type::CUSTOM_RULE,
                        'amount'              => $points,
                        'balance'             => $_customer->getMwRewardPoint(),
                        'transaction_detail'  => $ruleId,
                        'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                        'expired_day'         => $expiredDay,
                        'expired_time'        => $expiredTime,
                        'point_remaining'     => $remainingPoints,
                        'status'              => Status::COMPLETE
                    ];
                    $_customer->saveTransactionHistory($historyData);

                    // Get store of customer
                    $store = $this->_objectManager->get(
                        'Magento\Store\Model\Store'
                    )->load($customer->getStoreId());

                    // Send mail when points changed
                    $dataHelper->sendEmailCustomerPointChanged(
                        $_customer->getId(),
                        $historyData,
                        $store->getCode()
                    );
                    $this->messageManager->addSuccess(
                        __('Congratulation! %1 Reward Points have been added to your account', $points)
                    );
                }
            }
        }

        if ($check == 0) {
            $this->messageManager->addError(__('Coupon code %1 invalid', $couponCode));
        }

        return $this->resultRedirectFactory->create()->setPath('rewardpoints/rewardpoints/index');
    }
}
