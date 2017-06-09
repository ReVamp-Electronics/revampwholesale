<?php

namespace MW\RewardPoints\Cron;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class ExpirationEmail
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
     * Send expiration points email to customer
     */
    public function execute()
    {
        $day           = (int) $this->_dataHelper->getExpirationDaysEmail();
        $to            = time() + $day * 24 * 3600;
        $transactions  = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('type_of_transaction', ['in' => Type::getAddPointArray()])
            ->addFieldToFilter('status', Status::COMPLETE)
            ->addFieldToFilter('DATE(expired_time)', ['eq' => date('Y-m-d', $to)])
            ->addFieldToFilter('point_remaining', ['gt' => 0])
            ->setOrder('expired_time', 'DESC')
            ->setOrder('history_id', 'ASC');

        foreach ($transactions as $transaction) {
            $customerId = $transaction->getCustomerId();
            $_customer  = $this->_memberFactory->create()->load($customerId);

            if ($_customer->getSubscribedPointExpiration() == 1) {
                $historyData = [
                    'type_of_transaction' => Type::EXPIRED_POINTS,
                    'amount'              => (int) $transaction->getPointRemaining(),
                    'balance'             => $_customer->getMwRewardPoint(),
                    'transaction_detail'  => $transaction->getHistoryId(),
                    'transaction_time'    => $transaction->getExpiredTime(),
                    'status'              => Status::COMPLETE
                ];

                $storeId = $this->_customerFactory->create()->load($customerId)->getStoreId();
                $store   = $this->_storeFactory->create()->load($storeId);

                $this->_dataHelper->sendEmailCustomerPointExpiration(
                    $customerId,
                    $historyData,
                    $store->getCode()
                );
            }
        }
    }
}
