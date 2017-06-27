<?php

namespace MW\RewardPoints\Cron;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class ExpiredPoint
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

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
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_storeFactory = $storeFactory;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
        $this->_historyFactory = $historyFactory;
    }

    /**
     * Substract expiration points of customers
     */
    public function execute()
    {
        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('type_of_transaction', ['in' => Type::getAddPointArray()])
            ->addFieldToFilter('status', Status::COMPLETE)
            ->addFieldToFilter('expired_time', ['neq' => null])
            ->addFieldToFilter('point_remaining', ['gt' => 0])
            ->addFieldToFilter('expired_time', ['to' => time(), 'date' => true])
            ->setOrder('expired_time', 'ASC')
            ->setOrder('history_id', 'DESC');

        foreach ($transactions as $transaction) {
            $customerId     = $transaction->getCustomerId();
            $storeId        = $this->_customerFactory->create()->load($customerId)->getStoreId();
            $remainingPoint = $transaction->getPointRemaining();
            $_customer      = $this->_memberFactory->create()->load($customerId);
            $_customer->addRewardPoint(-$remainingPoint);
            $historyData = [
                'type_of_transaction' => Type::EXPIRED_POINTS,
                'amount'              => (int) $remainingPoint,
                'balance'             => $_customer->getMwRewardPoint(),
                'transaction_detail'  => $transaction->getHistoryId(),
                'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                'status'              => Status::COMPLETE
            ];

            $_customer->saveTransactionHistory($historyData);

            $transaction->setPointRemaining(0)
                ->setExpiredTime(null)
                ->save();

            $store = $this->_storeFactory->create()->load($storeId);
            $this->_dataHelper->sendEmailCustomerPointChanged(
                $_customer->getId(),
                $historyData,
                $store->getCode()
            );
        }
    }
}
