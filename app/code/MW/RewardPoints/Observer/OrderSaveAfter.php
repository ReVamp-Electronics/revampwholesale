<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

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
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
    ) {
        $this->_storeFactory = $storeFactory;
        $this->_orderFactory = $orderFactory;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
        $this->_historyFactory = $historyFactory;
    }

    /**
     * Calculate reward points after save order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $storeCode = $this->_storeFactory->create()->load($order->getStoreId())->getCode();
        $statusAddRewardPoint = $this->_dataHelper->getStatusAddRewardPointStore($storeCode);

        if ($order->getStatus() == 'canceled') {
            $this->cancelOrder($observer);
        }

        if ($order->getStatus() == $statusAddRewardPoint) {
            $this->completeOrder($observer);
        }

        if ($order->getStatus() == 'closed') {
            $this->refundOrder($observer);
        }
    }

    /**
     * Update reward points when payment failed (Canceled)
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function cancelOrder($observer)
    {
        $order = $observer->getOrder();
        $storeCode = $this->_storeFactory->create()->load($order->getStoreId())->getCode();
        $customerId = $order->getCustomerId();
        $customer = $this->_memberFactory->create()->load($customerId);

        $data = ['status_check' => Status::REFUNDED];
        $collection = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('history_order_id', $order->getId());
        foreach ($collection as $item) {
            $model = $this->_historyFactory->create()->load($item->getId());
            $model->addData($data)
                ->setId($item->getId())
                ->save();
        }

        $newTransactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('transaction_detail', ['like' => '%'.$order->getIncrementId().'%'])
            ->addFieldToFilter('status', ['eq' => Status::COMPLETE])
            ->addOrder('transaction_time', 'ASC')
            ->addOrder('history_id', 'ASC');
        $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());

        foreach ($newTransactions as $newTransaction) {
            switch ($newTransaction->getTypeOfTransaction()) {
                // Use points to check out
                case Type::USE_TO_CHECKOUT: {
                    if ($newTransaction->getTransactionDetail() != $order->getIncrementId()) {
                        continue;
                    }

                    $refundedTransactions = $this->_historyFactory->create()->getCollection()
                        ->addFieldToFilter('type_of_transaction', Type::ORDER_CANCELLED_ADD_POINTS)
                        ->addFieldToFilter('transaction_detail', $order->getIncrementId());

                    if (sizeof($refundedTransactions) > 0) {
                        continue;
                    }

                    $customer->addRewardPoint($newTransaction->getAmount());
                    $expiredDay      = $newTransaction->getExpiredDay();
                    $results         = $this->_dataHelper->getTransactionByExpiredDayAndPoints(
                        (int)$newTransaction->getAmount(),
                        $expiredDay
                    );
                    $expiredTime     = $results[0];
                    $remainingPoints = $results[1];
                    $historyData = [
                        'type_of_transaction' => Type::ORDER_CANCELLED_ADD_POINTS,
                        'amount' => (int) $newTransaction->getAmount(),
                        'balance' => $customer->getMwRewardPoint(),
                        'transaction_detail' => $order->getIncrementId(),
                        'transaction_time' => $now,
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoints,
                        'status' => Status::COMPLETE,
                        'status_check' => Status::REFUNDED
                    ];

                    $customer->saveTransactionHistory($historyData);

                    // Send mail when points changed
                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $customer->getId(),
                        $historyData,
                        $storeCode
                    );
                    break;
                }
            }
        }

        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('transaction_detail', ['like' => '%'.$order->getIncrementId().'%'])
            ->addFieldToFilter('status', ['eq' => Status::PENDING])
            ->addOrder('transaction_time', 'ASC')
            ->addOrder('history_id', 'ASC');

        foreach ($transactions as $transaction) {
            switch ($transaction->getTypeOfTransaction()) {
                // Points for product
                case Type::PURCHASE_PRODUCT:
                    $detail = explode("|", $transaction->getTransactionDetail());
                    if ($detail[1] != $order->getId()) {
                        continue;
                    }

                    $transaction->setTransactionTime($now)
                        ->setBalance($customer->getRewardPoint())
                        ->setStatus(Status::UNCOMPLETE)
                        ->save();
                    break;

                // Add points when first purchase, next purchase
                case Type::FRIEND_FIRST_PURCHASE:
                case Type::FRIEND_NEXT_PURCHASE:
                    $detail = explode("|",$transaction->getTransactionDetail());
                    if ($detail[1] != $order->getId()) {
                        continue;
                    }
                    $transaction->setBalance($customer->getFriend()->getMwRewardPoint())
                        ->setTransactionTime($now)
                        ->setStatus(Status::UNCOMPLETE)
                        ->save();
                    break;

                // Use points to check out
                case Type::USE_TO_CHECKOUT:
                    if ($transaction->getTransactionDetail() != $order->getIncrementId()) {
                        continue;
                    }

                    $refundedTransactions = $this->_historyFactory->create()->getCollection()
                        ->addFieldToFilter('type_of_transaction', Type::ORDER_CANCELLED_ADD_POINTS)
                        ->addFieldToFilter('transaction_detail', $order->getIncrementId());

                    if (sizeof($refundedTransactions) > 0) {
                        continue;
                    }

                    $customer->addRewardPoint($transaction->getAmount());
                    $expiredDay      = $transaction->getExpiredDay();
                    $results         = $this->_dataHelper->getTransactionByExpiredDayAndPoints(
                        (int)$transaction->getAmount(),
                        $expiredDay
                    );
                    $expiredTime     = $results[0];
                    $remainingPoints = $results[1];
                    $historyData = [
                        'type_of_transaction' => Type::ORDER_CANCELLED_ADD_POINTS,
                        'amount' => (int) $transaction->getAmount(),
                        'balance' => $customer->getMwRewardPoint(),
                        'transaction_detail' => $order->getIncrementId(),
                        'transaction_time' => $now,
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoints,
                        'status' => Status::COMPLETE,
                        'status_check' => Status::REFUNDED
                    ];
                    $customer->saveTransactionHistory($historyData);
                    // Send mail when points changed
                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $customer->getId(),
                        $historyData,
                        $storeCode
                    );
                    break;

                // Reward points for order
                case Type::CHECKOUT_ORDER:
                    if ($transaction->getTransactionDetail() != $order->getIncrementId()) {
                        continue;
                    }

                    $transaction->setBalance($customer->getMwRewardPoint())
                        ->setTransactionTime($now)
                        ->setStatus(Status::UNCOMPLETE)
                        ->save();
                    break;

                case Type::CHECKOUT_ORDER_NEW:
                    $detail = explode("||", $transaction->getTransactionDetail());
                    if ($detail[0] != $order->getIncrementId()) {
                        continue;
                    }

                    $transaction->setBalance($customer->getMwRewardPoint())
                        ->setTransactionTime($now)
                        ->setStatus(Status::UNCOMPLETE)
                        ->save();
                    break;
            }
        }

        $friendId = $customer->getMwFriendId();
        if ($friendId) {
            $friend = $this->_memberFactory->create()->load($friendId);

            // Update transaction status for friend
            $transactions = $this->_historyFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $friendId)
                ->addFieldToFilter('status', Status::PENDING)
                ->addFieldToFilter('type_of_transaction', [
                    'in' => [
                        Type::FRIEND_FIRST_PURCHASE,
                        Type::FRIEND_NEXT_PURCHASE
                    ]
                ])
                ->addOrder('transaction_time', 'ASC')
                ->addOrder('history_id', 'ASC');

            foreach ($transactions as $transaction) {
                $detail = explode("|",$transaction->getTransactionDetail());
                if ($detail[1] != $order->getId()) {
                    continue;
                }

                $transaction->setBalance($friend->getMwRewardPoint())
                    ->setTransactionTime($now)
                    ->setStatus(Status::UNCOMPLETE)
                    ->save();
            }
        }
    }

    /**
     * Update reward points when complete an order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function completeOrder($observer)
    {
        $order      = $observer->getOrder();
        $storeCode  = $this->_storeFactory->create()->load($order->getStoreId())->getCode();
        $customerId = $order->getCustomerId();
        $customer   = $this->_memberFactory->create()->load($customerId);

        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', Status::PENDING)
            ->addOrder('transaction_time', 'ASC')
            ->addOrder('history_id', 'ASC');
        $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());

        foreach ($transactions as $transaction) {
            switch ($transaction->getTypeOfTransaction()) {
                // Points for product
                case Type::PURCHASE_PRODUCT:
                    $detail = explode("|", $transaction->getTransactionDetail());
                    if ($detail[1] != $order->getIncrementId()) {
                        continue;
                    }

                    $customer->addRewardPoint($transaction->getAmount());
                    $transaction->setTransactionTime($now)
                        ->setBalance($customer->getRewardPoint())
                        ->setStatus(Status::COMPLETE)
                        ->save();

                    // Send mail when points changed
                    $historyData = [
                        'type_of_transaction' => $transaction->getTypeOfTransaction(),
                        'amount'              => (int)$transaction->getAmount(),
                        'balance'             => $transaction->getBalance(),
                        'transaction_detail'  => $transaction->getTransactionDetail(),
                        'transaction_time'    => $transaction->getTransactionTime(),
                        'status'              => $transaction->getStatus()
                    ];

                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $customer->getId(),
                        $historyData,
                        $storeCode
                    );
                    break;

                // Use points to check out
                case Type::USE_TO_CHECKOUT:
                    $order = $this->_orderFactory->create()->loadByIncrementId(
                        $transaction->getTransactionDetail()
                    );

                    if ($transaction->getTransactionDetail() != $order->getIncrementId()) {
                        continue;
                    }

                    $transaction->setTransactionTime($now)
                        ->setStatus(Status::COMPLETE)
                        ->save();
                    break;

                // Reward points for order
                case Type::CHECKOUT_ORDER:
                    if ($transaction->getTransactionDetail() != $order->getIncrementId()) {
                        continue;
                    }

                    $customer->addRewardPoint($transaction->getAmount());
                    $transaction->setBalance($customer->getMwRewardPoint())
                        ->setTransactionTime($now)
                        ->setStatus(Status::COMPLETE)
                        ->save();

                    // Send mail when points changed
                    $historyData = [
                        'type_of_transaction' => $transaction->getTypeOfTransaction(),
                        'amount' => (int) $transaction->getAmount(),
                        'balance' => $transaction->getBalance(),
                        'transaction_detail' => $transaction->getTransactionDetail(),
                        'transaction_time' => $transaction->getTransactionTime(),
                        'status' => $transaction->getStatus()
                    ];

                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $customer->getId(),
                        $historyData,
                        $storeCode
                    );
                    break;

                case Type::CHECKOUT_ORDER_NEW:
                    $detail = explode("||", $transaction->getTransactionDetail());
                    if ($detail[0] != $order->getIncrementId()) {
                        continue;
                    }

                    $customer->addRewardPoint($transaction->getAmount());
                    $expiredDay      = $transaction->getExpiredDay();
                    $results         = $this->_dataHelper->getTransactionByExpiredDayAndPoints(
                        (int)$transaction->getAmount(),
                        $expiredDay
                    );
                    $expiredTime     = $results[0];
                    $remainingPoints = $results[1];
                    $transaction->setExpiredTime($expiredTime)
                        ->setPointRemaining($remainingPoints)
                        ->setBalance($customer->getMwRewardPoint())
                        ->setTransactionTime($now)
                        ->setStatus(Status::COMPLETE)
                        ->save();

                    // Send mail when points changed
                    $historyData = [
                        'type_of_transaction' => $transaction->getTypeOfTransaction(),
                        'amount' => (int) $transaction->getAmount(),
                        'balance' => $transaction->getBalance(),
                        'transaction_detail' => $transaction->getTransactionDetail(),
                        'transaction_time' => $transaction->getTransactionTime(),
                        'status' => $transaction->getStatus()
                    ];

                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $customer->getId(),
                        $historyData,
                        $storeCode
                    );
                    break;
            }
        }

        $friendId = $customer->getMwFriendId();
        if ($friendId) {
            $friend = $this->_memberFactory->create()->load($friendId);

            // Update transaction status for friend
            $transactions = $this->_historyFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $friendId)
                ->addFieldToFilter('status', Status::PENDING)
                ->addFieldToFilter('type_of_transaction', [
                    'in' => [
                        Type::FRIEND_FIRST_PURCHASE,
                        Type::FRIEND_NEXT_PURCHASE
                    ]
                ])
                ->addOrder('transaction_time', 'ASC')
                ->addOrder('history_id', 'ASC');

            foreach ($transactions as $transaction) {
                $detail = explode("|", $transaction->getTransactionDetail());
                if ($detail[1] != $order->getId()) {
                    continue;
                }

                $friend->addRewardPoint($transaction->getAmount());
                $expiredDay      = $transaction->getExpiredDay();
                $results         = $this->_dataHelper->getTransactionByExpiredDayAndPoints(
                    (int)$transaction->getAmount(),
                    $expiredDay
                );
                $expiredTime     = $results[0];
                $remainingPoints = $results[1];
                $transaction->setExpiredTime($expiredTime)
                    ->setPointRemaining($remainingPoints)
                    ->setBalance($friend->getMwRewardPoint())
                    ->setTransactionTime($now)
                    ->setStatus(Status::COMPLETE)
                    ->save();

                // Send mail when points changed
                $historyData = [
                    'type_of_transaction' => $transaction->getTypeOfTransaction(),
                    'amount' => (int) $transaction->getAmount(),
                    'balance' => $transaction->getBalance(),
                    'transaction_detail' => $transaction->getTransactionDetail(),
                    'transaction_time' => $transaction->getTransactionTime(),
                    'status' => $transaction->getStatus()
                ];
                $this->_dataHelper->sendEmailCustomerPointChanged(
                    $friendId,
                    $historyData,
                    $storeCode
                );
            }
        }
    }

    /**
     * Update reward points when refund an order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function refundOrder($observer)
    {
        $order        = $observer->getOrder();
        $storeCode    = $this->_storeFactory->create()->load($order->getStoreId())->getCode();
        $now          = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('transaction_detail', ['like' => '%'.$order->getIncrementId().'%'])
            ->addFieldToFilter('status', ['eq' => Status::COMPLETE]);

        if (sizeof($transactions) > 0) {
            foreach ($transactions as $transaction) {
                $customer = $transaction->getCustomer();
                $subtractRewardPoints = $this->_dataHelper->getSubtractPointWhenRefundConfigStore($storeCode);
                $restoreSpentPoints = $this->_dataHelper->getRestoreSpentPointsWhenRefundConfigStore($storeCode);

                switch ($transaction->getTypeOfTransaction()) {
                    // Points for product
                    case Type::PURCHASE_PRODUCT:
                        if ($transaction->getStatus() == Status::COMPLETE) {
                            $customer->addRewardPoint(-$transaction->getAmount());

                            $historyData = [
                                'type_of_transaction' => Type::REFUND_ORDER_SUBTRACT_PRODUCT_POINTS,
                                'amount'              => (int)$transaction->getAmount(),
                                'balance'             => $customer->getMwRewardPoint(),
                                'transaction_detail'  => $transaction->getTransactionDetail(),
                                'transaction_time'    => $now,
                                'status'              => Status::COMPLETE,
                                'status_check'        => Status::REFUNDED
                            ];

                            $customer->saveTransactionHistory($historyData);

                            // Send mail when points changed
                            $this->_dataHelper->sendEmailCustomerPointChanged(
                                $customer->getId(),
                                $historyData,
                                $storeCode
                            );
                        }
                        break;

                    // Use points to check out
                    case Type::USE_TO_CHECKOUT:
                        if ($transaction->getTransactionDetail() != $order->getIncrementId()) {
                            continue;
                        }

                        $refundedTransactions = $this->_historyFactory->create()->getCollection()
                            ->addFieldToFilter('type_of_transaction', Type::REFUND_ORDER_ADD_POINTS)
                            ->addFieldToFilter('transaction_detail', $order->getIncrementId());

                        if (sizeof($refundedTransactions) > 0) {
                            continue;
                        }

                        if ($restoreSpentPoints) {
                            $customer->addRewardPoint($transaction->getAmount());
                            $expiredDay      = $transaction->getExpiredDay();
                            $results         = $this->_dataHelper->getTransactionByExpiredDayAndPoints(
                                (int)$transaction->getAmount(),
                                $expiredDay
                            );
                            $expiredTime     = $results[0];
                            $remainingPoints = $results[1];
                            $historyData = [
                                'type_of_transaction' => Type::REFUND_ORDER_ADD_POINTS,
                                'amount' => (int) $transaction->getAmount(),
                                'balance' => $customer->getMwRewardPoint(),
                                'transaction_detail' => $order->getIncrementId(),
                                'transaction_time' => $now,
                                'expired_day' => $expiredDay,
                                'expired_time' => $expiredTime,
                                'point_remaining' => $remainingPoints,
                                'status' => Status::COMPLETE,
                                'status_check' => Status::REFUNDED
                            ];
                            $customer->saveTransactionHistory($historyData);

                            // Send mail when points changed
                            $this->_dataHelper->sendEmailCustomerPointChanged(
                                $customer->getId(),
                                $historyData,
                                $storeCode
                            );
                        }
                        break;

                    // Reward points for order
                    case Type::CHECKOUT_ORDER:
                        if ($transaction->getTransactionDetail() != $order->getIncrementId()) {
                            continue;
                        }

                        $refundedTransactions = $this->_historyFactory->create()->getCollection()
                            ->addFieldToFilter('type_of_transaction', Type::REFUND_ORDER_SUBTRACT_POINTS)
                            ->addFieldToFilter('transaction_detail', $order->getIncrementId());

                        if (sizeof($refundedTransactions) > 0) {
                            continue;
                        }

                        if ($transaction->getStatus() == Status::COMPLETE && $subtractRewardPoints) {
                            $customer->addRewardPoint(-$transaction->getAmount());
                            $historyData = [
                                'type_of_transaction' => Type::REFUND_ORDER_SUBTRACT_POINTS,
                                'amount' => (int) $transaction->getAmount(),
                                'balance' => $customer->getMwRewardPoint(),
                                'transaction_detail' => $order->getIncrementId(),
                                'transaction_time' => $now,
                                'status' => Status::COMPLETE,
                                'status_check' => Status::REFUNDED
                            ];
                            $customer->saveTransactionHistory($historyData);

                            // Send mail when points changed
                            $this->_dataHelper->sendEmailCustomerPointChanged(
                                $customer->getId(),
                                $historyData,
                                $storeCode
                            );
                        }
                        break;

                    // Reward points for order
                    case Type::CHECKOUT_ORDER_NEW:
                        $detail = explode("||", $transaction->getTransactionDetail());
                        if ($detail[0] != $order->getIncrementId()) {
                            continue;
                        }

                        $refundedTransactions = $this->_historyFactory->create()->getCollection()
                            ->addFieldToFilter('type_of_transaction', Type::REFUND_ORDER_SUBTRACT_POINTS)
                            ->addFieldToFilter('transaction_detail', $order->getIncrementId());

                        if (sizeof($refundedTransactions) > 0) {
                            continue;
                        }

                        if ($transaction->getStatus() == Status::COMPLETE && $subtractRewardPoints) {
                            $customer->addRewardPoint(-$transaction->getAmount());
                            $historyData = [
                                'type_of_transaction' => Type::REFUND_ORDER_SUBTRACT_POINTS,
                                'amount' => (int) $transaction->getAmount(),
                                'balance' => $customer->getMwRewardPoint(),
                                'transaction_detail' => $order->getIncrementId(),
                                'transaction_time' => $now,
                                'status' => Status::COMPLETE,
                                'status_check' => Status::REFUNDED
                            ];
                            $customer->saveTransactionHistory($historyData);

                            // Process expired points when spent point
                            $this->_dataHelper->processExpiredPointsWhenSpentPoints(
                                $customer->getId(),
                                $transaction->getAmount()
                            );

                            // Send mail when points changed
                            $this->_dataHelper->sendEmailCustomerPointChanged(
                                $customer->getId(),
                                $historyData,
                                $storeCode
                            );
                        }
                        break;
                }
            }
        }

        $customerId = $order->getCustomerId();
        $friendId = $this->_memberFactory->create()->load($customerId)->getMwFriendId();
        if ($friendId) {
            $friend = $this->_memberFactory->create()->load($friendId);

            // Update transaction status for friend
            $transactions = $this->_historyFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $friendId)
                ->addFieldToFilter('status', Status::COMPLETE)
                ->addFieldToFilter('type_of_transaction', [
                    'in' => [
                        Type::FRIEND_FIRST_PURCHASE,
                        Type::FRIEND_NEXT_PURCHASE
                    ]
                ])
                ->addOrder('transaction_time', 'ASC')
                ->addOrder('history_id', 'ASC');

            foreach ($transactions as $transaction) {
                $subtractRewardPoints = $this->_dataHelper->getSubtractPointWhenRefundConfigStore($storeCode);

                $detail = explode("|", $transaction->getTransactionDetail());
                if ($detail[1] != $order->getId()) {
                    continue;
                }

                $refundedTransactions = $this->_historyFactory->create()->getCollection()
                    ->addFieldToFilter('type_of_transaction', Type::REFUND_ORDER_FREND_PURCHASE)
                    ->addFieldToFilter('transaction_detail', $transaction->getTransactionDetail());

                if (sizeof($refundedTransactions) > 0) {
                    continue;
                }

                if ($subtractRewardPoints) {
                    $friend->addRewardPoint(-$transaction->getAmount());
                    $historyData = [
                        'type_of_transaction' => Type::REFUND_ORDER_FREND_PURCHASE,
                        'amount' => (int) $transaction->getAmount(),
                        'balance' => $friend->getMwRewardPoint(),
                        'transaction_detail' => $transaction->getTransactionDetail(),
                        'transaction_time' => $now,
                        'status' => Status::COMPLETE,
                        'status_check' => Status::REFUNDED
                    ];
                    $friend->saveTransactionHistory($historyData);

                    // Process expired points when spent point
                    $this->_dataHelper->processExpiredPointsWhenSpentPoints(
                        $friendId,
                        $transaction->getAmount()
                    );

                    // Send mail when points changed
                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $friendId,
                        $historyData,
                        $storeCode
                    );
                }
            }
        }
    }
}