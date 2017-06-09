<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber as NewsletterSubscriber;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class NewsletterSubscriberSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param NewsletterSubscriber $subscriber
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        NewsletterSubscriber $subscriber,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_subscriber = $subscriber;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Add transaction after saving subscriber
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $subscriber = $observer->getSubscriber();
        $store = $this->_storeManager->getStore();
        $customerGroupId = $this->_customerFactory->create()->load($subscriber->getCustomerId())->getGroupId();
        $results = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
            Type::SIGNING_UP_NEWLETTER,
            $customerGroupId,
            $store->getId()
        );

        if ($subscriber->getCustomerId()
            && $this->_dataHelper->checkCustomerMaxBalance($subscriber->getCustomerId(), $store->getId(), $results[0])
        ) {
            $rewardpoints    = $results[0];
            $expiredDay      = $results[1];
            $expiredTime     = $results[2];
            $remainingPoints = $results[3];
            $this->_dataHelper->checkAndInsertCustomerId($subscriber->getCustomerId(), 0);
            $_customer = $this->_memberFactory->create()->load($subscriber->getCustomerId());
            $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());

            if ($subscriber->getId()) {
                $oldSubscriber = $this->_subscriber->load($subscriber->getId());
                if ($oldSubscriber->getStatus() == NewsletterSubscriber::STATUS_NOT_ACTIVE
                    && $subscriber->getStatus() == NewsletterSubscriber::STATUS_SUBSCRIBED
                ) {
                    if ($rewardpoints) {
                        $_customer->addRewardPoint($rewardpoints);
                        $historyData = [
                            'type_of_transaction' => Type::SIGNING_UP_NEWLETTER,
                            'amount' => (int) $rewardpoints,
                            'balance' => $_customer->getMwRewardPoint(),
                            'transaction_detail' => '',
                            'transaction_time' => $now,
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
            } else {
                if ($subscriber->getStatus() == NewsletterSubscriber::STATUS_SUBSCRIBED) {
                    if ($rewardpoints) {
                        $_customer->addRewardPoint($rewardpoints);
                        $historyData = [
                            'type_of_transaction' => Type::SIGNING_UP_NEWLETTER,
                            'amount' => (int) $rewardpoints,
                            'balance' => $_customer->getMwRewardPoint(),
                            'transaction_detail' => '',
                            'transaction_time' => $now,
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
