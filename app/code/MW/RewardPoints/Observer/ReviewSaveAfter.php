<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class ReviewSaveAfter implements ObserverInterface
{
    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_historyFactory = $historyFactory;
        $this->_memberFactory = $memberFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_storeFactory = $storeFactory;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Add transaction after saving review
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $review = $observer->getObject();
        $storeId = $review->getStoreId();
        $customerGroupId = $this->_customerFactory->create()->load($review->getCustomerId())->getGroupId();
        $results = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
            Type::SUBMIT_PRODUCT_REVIEW,
            $customerGroupId,
            $storeId
        );

        if ($this->_dataHelper->moduleEnabled()
            && $review->getCustomerId()
            && $this->_dataHelper->checkCustomerMaxBalance($review->getCustomerId(), $storeId, $results[0])
        ) {
            $transactions = $this->_historyFactory->create()->getCollection()
                ->addFieldToFilter('type_of_transaction', Type::SUBMIT_PRODUCT_REVIEW)
                ->addFieldToFilter('transaction_detail', $review->getId().'|'.$review->getEntityPkValue());

            if (!sizeof($transactions)) {
                $this->_dataHelper->checkAndInsertCustomerId($review->getCustomerId(), 0);
                $_customer = $this->_memberFactory->create()->load($review->getCustomerId());
                $points          = $results[0];
                $expiredDay      = $results[1];
                $expiredTime     = $results[2];
                $remainingPoints = $results[3];

                if ($review->getStatusId() == Review::STATUS_APPROVED && $points) {
                    $_customer->addRewardPoint($points);
                    $historyData = [
                        'type_of_transaction' => Type::SUBMIT_PRODUCT_REVIEW,
                        'amount' => $points,
                        'balance' => $_customer->getMwRewardPoint(),
                        'transaction_detail' => $review->getId().'|'.$review->getEntityPkValue(),
                        'transaction_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoints,
                        'status' => Status::COMPLETE
                    ];
                    $_customer->saveTransactionHistory($historyData);

                    // Send mail when points changed
                    $storeCode = $this->_storeFactory->create()->load($storeId)->getCode();
                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $_customer->getId(),
                        $historyData,
                        $storeCode
                    );
                }
            }
        }
    }
}
