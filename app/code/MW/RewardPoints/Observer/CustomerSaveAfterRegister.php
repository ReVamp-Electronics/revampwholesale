<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\RewardPoints\Model\Status;
use MW\RewardPoints\Model\Type;

class CustomerSaveAfterRegister implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Helper\Rules
     */
    protected $_rulesHelper;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Helper\Rules $rulesHelper
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Helper\Rules $rulesHelper,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
    ) {
        $this->_storeFactory = $storeFactory;
        $this->_customerFactory = $customerFactory;
        $this->_cookieManager = $cookieManager;
        $this->_messageManager = $messageManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_dataHelper = $dataHelper;
        $this->_rulesHelper = $rulesHelper;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_memberFactory = $memberFactory;
        $this->_historyFactory = $historyFactory;
    }

    /**
     * Update point when sign up or friend register custom rule
     *
     * @param  $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->moduleEnabled()) {
            $customer = $observer->getCustomer();
            if ($customer) {
                $customerId = $customer->getId();
                $storeId 	= $customer->getStoreId();
            } else {
                $customerId = $observer->getCustomerId();
                $storeId 	= $observer->getStoreId();
            }
            $store = $this->_storeFactory->create()->load($storeId);

            if ($customerId) {
                $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
                $customerGroupId = $this->_customerFactory->create()->load($customerId)->getGroupId();

                // Check invition information if exist add reward point to friend
                $friendId = $this->_cookieManager->getCookie('friend');
                if ($friendId) {
                    $this->_dataHelper->checkAndInsertCustomerId($friendId, 0);
                    $friend = $this->_memberFactory->create()->load($friendId);

                    $results = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                        Type::FRIEND_REGISTERING,
                        $customerGroupId,
                        $storeId
                    );

                    $point          = $results[0];
                    $expiredDay     = $results[1];
                    $expiredTime    = $results[2];
                    $remainingPoint = $results[3];
                    $sizeHistory = $this->_historyFactory->create()->sizeofTransactionHistory(
                        $friendId,
                        Type::FRIEND_REGISTERING,
                        $customerId
                    );

                    if ($friend->getId()
                        && $point
                        && $sizeHistory == 0
                        && $this->_dataHelper->checkCustomerMaxBalance($friend->getId(), $store->getCode(), $point)
                    ) {
                        $friend->setMwRewardPoint($friend->getMwRewardPoint() + $point);
                        $friend->save();
                        $historyData = [
                            'type_of_transaction' => Type::FRIEND_REGISTERING,
                            'amount' => $point,
                            'balance' => $friend->getMwRewardPoint(),
                            'transaction_detail' => $customerId,
                            'transaction_time' => $now,
                            'expired_day' => $expiredDay,
                            'expired_time' => $expiredTime,
                            'point_remaining' => $remainingPoint,
                            'status' => Status::COMPLETE
                        ];
                        $friend->saveTransactionHistory($historyData);

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $friend->getId(),
                            $historyData,
                            $store->getCode()
                        );
                    }
                }

                // Init reward points of customer
                $results  = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                    Type::REGISTERING,
                    $customerGroupId,
                    $storeId
                );
                $point           = $results[0];
                $expiredDay      = $results[1];
                $expiredTime     = $results[2];
                $remainingPoint  = $results[3];
                $this->_dataHelper->checkAndInsertCustomerId($customerId, $friendId);
                $sizeHistory = $this->_historyFactory->create()->sizeofTransactionHistory(
                    $customerId,
                    Type::REGISTERING
                );

                // Add point when customer Subcriber
                $this->updatePointSubcriber($customerId);

                // Save history transaction
                if ($point && $sizeHistory == 0
                    && $this->_dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $point)
                ) {
                    /** @var \MW\RewardPoints\Model\Customer $_customer */
                    $_customer = $this->_memberFactory->create()->load($customerId);
                    $_customer->setMwRewardPoint($_customer->getMwRewardPoint() + $point);
                    $_customer->save();
                    $historyData = [
                        'type_of_transaction' => Type::REGISTERING,
                        'amount' => $point,
                        'balance' => $_customer->getMwRewardPoint(),
                        'transaction_detail' => '',
                        'transaction_time' => $now,
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoint,
                        'status' => Status::COMPLETE
                    ];
                    $_customer->saveTransactionHistory($historyData);

                    // Send mail when points changed
                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $customerId,
                        $historyData,
                        $store->getCode()
                    );
                    $this->_messageManager->addSuccess(
                        __('You received %1 %2 for signing up.', $point, $this->_dataHelper->getPointCurency($store->getCode()))
                    );
                }
            }

            // Update point when send point a friend (register success)
            $this->updateNew($customerId, $storeId);

            // Add point when type custom rule mw_rule (register success)
            $this->customerRegister($customerId, $store);
        }
    }

    /**
     * Add point when customer Subcriber
     *
     * @param  int $customerId
     * @return void
     */
    public function updatePointSubcriber($customerId)
    {
        $subscriber = $this->_subscriberFactory->create()->getCollection()
            ->useOnlyCustomers()
            ->useOnlySubscribed()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();
        $customerId = (int) $subscriber->getCustomerId();

        // Check customer is subscriberd or not
        if ($customerId) {
            $customerGroupId = $this->_customerFactory->create()->load($customerId)->getGroupId();
            $store = $this->_storeFactory->create()->load($subscriber->getStoreId());
            $results = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                Type::SIGNING_UP_NEWLETTER,
                $customerGroupId,
                $store->getId()
            );
            $rewardpoints   = $results[0];
            $expiredDay     = $results[1];
            $expiredTime    = $results[2];
            $remainingPoint = $results[3];

            $this->_dataHelper->checkAndInsertCustomerId($customerId, 0);

            /** @var \MW\RewardPoints\Model\Customer $_customer */
            $_customer = $this->_memberFactory->create()->load($customerId);
            $sizeHistory = $this->_historyFactory->create()->sizeofTransactionHistory(
                $customerId,
                Type::SIGNING_UP_NEWLETTER
            );

            if ($rewardpoints && $sizeHistory == 0) {
                $_customer->addRewardPoint($rewardpoints);
                $historyData = [
                    'type_of_transaction' => Type::SIGNING_UP_NEWLETTER,
                    'amount' => (int) $rewardpoints,
                    'balance' => $_customer->getMwRewardPoint(),
                    'transaction_detail' => '',
                    'transaction_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                    'expired_day' => $expiredDay,
                    'expired_time' => $expiredTime,
                    'point_remaining' => $remainingPoint,
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

    /**
     * Update point when send point a friend (register success)
     *
     * @param $customerId
     * @param $storeId
     */
    protected function updateNew($customerId, $storeId)
    {
        $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
        $store = $this->_storeFactory->create()->load($storeId);
        $friendId = $this->_cookieManager->getCookie('friend');

        $customer     = $this->_memberFactory->create()->load($customerId);
        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('status', Status::PENDING)
            ->addOrder('transaction_time', 'ASC')
            ->addOrder('history_id', 'ASC');

        // Because select by current customer so have no record
        foreach ($transactions as $transaction) {
            switch ($transaction->getTypeOfTransaction()) {
                case Type::SEND_TO_FRIEND:
                    // If the time is expired add reward points back to customer
                    $oldtime     = strtotime($transaction->getTransactionTime());
                    $currentTime = strtotime($now);
                    $hour        = ($currentTime - $oldtime) / (60 * 60);
                    $hourConfig  = $this->_dataHelper->timeLifeSendRewardPointsToFriend($store->getCode());
                    if ($hourConfig && ($hour > $hourConfig)) {
                        $this->_dataHelper->checkAndInsertCustomerId($customer->getId(), $friendId);
                        $customer->addRewardPoint($transaction->getAmount());
                        $results         = $this->_dataHelper->getTransactionExpiredPoints(
                            $transaction->getAmount(),
                            $store->getCode()
                        );
                        $expiredDay      = $results[0];
                        $expiredTime     = $results[1];
                        $remainingPoints = $results[2];

                        $historyData = [
                            'type_of_transaction' => Type::SEND_TO_FRIEND_EXPIRED,
                            'amount'              => (int)$transaction->getAmount(),
                            'balance'             => $customer->getMwRewardPoint(),
                            'transaction_detail'  => $transaction->getTransactionDetail(),
                            'transaction_time'    => $now,
                            'expired_day'         => $expiredDay,
                            'expired_time'        => $expiredTime,
                            'point_remaining'     => $remainingPoints,
                            'status'              => Status::COMPLETE
                        ];

                        $customer->saveTransactionHistory($historyData);
                        $transaction->setStatus(Status::UNCOMPLETE);
                        $transaction->save();

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $customer->getId(),
                            $historyData,
                            $store->getCode()
                        );
                    }
                    break;
            }
        }

        $_transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('transaction_detail', $customer->getCustomerModel()->getEmail())
            ->addFieldToFilter('type_of_transaction', Type::SEND_TO_FRIEND)
            ->addFieldToFilter('status', Status::PENDING);

        if (sizeof($_transactions)) {
            foreach ($_transactions as $_transaction) {
                $this->_dataHelper->checkAndInsertCustomerId($customer->getId(), $friendId);
                $customer->addRewardPoint($_transaction->getAmount());

                $results         = $this->_dataHelper->getTransactionExpiredPoints(
                    $_transaction->getAmount(),
                    $store->getCode()
                );
                $expiredDay      = $results[0];
                $expiredTime     = $results[1];
                $remainingPoints = $results[2];

                $historyData = [
                    'type_of_transaction' => Type::RECIVE_FROM_FRIEND,
                    'amount'              => $_transaction->getAmount(),
                    'balance'             => $customer->getMwRewardPoint(),
                    'transaction_detail'  => $_transaction->getCustomerId(),
                    'transaction_time'    => $now,
                    'expired_day'         => $expiredDay,
                    'expired_time'        => $expiredTime,
                    'point_remaining'     => $remainingPoints,
                    'status'              => Status::COMPLETE
                ];
                $customer->saveTransactionHistory($historyData);
                $_transaction->setStatus(Status::COMPLETE)
                    ->setTransactionDetail($customer->getCustomerId())
                    ->save();

                // Send mail when points changed
                $this->_dataHelper->sendEmailCustomerPointChanged(
                    $customer->getId(),
                    $historyData,
                    $store->getCode()
                );
            }
        }
    }

    /**
     * Add point when type custom rule mw_rule (register success)
     *
     * @param $customerId
     * @param $store
     */
    protected function customerRegister($customerId, $store)
    {
        $ruleId = (int) $this->_cookieManager->getCookie('mw_reward_rule');

        if ($ruleId) {
            $this->_rulesHelper->processCustomRule($customerId, Type::CUSTOM_RULE, $ruleId, $store);
            $this->_cookieManager->deleteCookie('mw_reward_rule');
        }
    }
}
