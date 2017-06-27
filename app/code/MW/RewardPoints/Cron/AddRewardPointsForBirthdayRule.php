<?php

namespace MW\RewardPoints\Cron;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class AddRewardPointsForBirthdayRule
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
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

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
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_storeFactory = $storeFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
        $this->_historyFactory = $historyFactory;
    }

    /**
     * Add reward points for birthday rule
     */
    public function execute()
    {
        // This collection get all users which have birthday on today
        $month    = date('m', (new \DateTime())->getTimestamp());
        $day      = date('d', (new \DateTime())->getTimestamp());
        $year     = date('Y', (new \DateTime())->getTimestamp());
        $customer = $this->_customerFactory->create()->getCollection();
        $customer->addFieldToFilter('dob', ['like' => '%' . $month . '-' . $day]);
        $items = $customer->getItems();

        if (sizeof($items) > 0) {
            foreach ($items as $item) {
                $storeId         = $item->getStoreId();
                $customerId      = $item->getEntityId();
                $customerGroupId = $item->getGroupId();
                $results         = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                    Type::CUSTOMER_BIRTHDAY,
                    $customerGroupId,
                    $storeId
                );
                $points         = $results[0];
                $expiredDay     = $results[1];
                $expiredTime    = $results[2];
                $remainingPoint = $results[3];
                $store          = $this->_storeFactory->create()->load($storeId);

                if ($points > 0 && $this->_dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $points)) {
                    $transactions = $this->_historyFactory->create()->getCollection()
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('type_of_transaction', Type::CUSTOMER_BIRTHDAY)
                        ->addFieldToFilter('transaction_detail', $year)
                        ->addFieldToFilter('status', ['eq' => Status::COMPLETE]);

                    if ($transactions->getSize() == 0) {
                        $this->_dataHelper->checkAndInsertCustomerId($customerId, 0);
                        $_customer = $this->_memberFactory->create()->load($customerId);
                        $_customer->addRewardPoint($points);
                        $historyData = [
                            'type_of_transaction' => Type::CUSTOMER_BIRTHDAY,
                            'amount'              => (int)$points,
                            'balance'             => $_customer->getMwRewardPoint(),
                            'transaction_detail'  => $year,
                            'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                            'expired_day'         => $expiredDay,
                            'expired_time'        => $expiredTime,
                            'point_remaining'     => $remainingPoint,
                            'status'              => Status::COMPLETE
                        ];

                        $_customer->saveTransactionHistory($historyData);

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $_customer->getId(),
                            $historyData,
                            $store->getCode()
                        );

                        // Send mail when customer birthday
                        $this->_dataHelper->sendEmailCustomerPointBirthday(
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
