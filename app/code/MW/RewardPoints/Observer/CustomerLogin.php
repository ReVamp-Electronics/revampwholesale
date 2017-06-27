<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class CustomerLogin implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \MW\RewardPoints\Helper\Rules
     */
    protected $_rulesHelper;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \MW\RewardPoints\Helper\Rules $rulesHelper
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MW\RewardPoints\Helper\Rules $rulesHelper
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_storeManager = $storeManager;
        $this->_memberFactory = $memberFactory;
        $this->_historyFactory = $historyFactory;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_rulesHelper = $rulesHelper;
    }

    /**
     * Process reward points when customer logs in
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Add point when custom rule type is login
        $customerId = $observer->getModel()->getId();
        $ruleId     = (int) $this->_cookieManager->getCookie('mw_reward_rule');

        if ($ruleId) {
            $store = $this->_storeManager->getStore();
            $this->_rulesHelper->processCustomRule($customerId, Type::CUSTOM_RULE, $ruleId, $store);
        }

        // Update point when send point a friend (login)
        $customer     = $this->_memberFactory->create()->load($observer->getModel()->getId());
        $store        = $this->_storeManager->getStore();
        $now          = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('status', Status::PENDING)
            ->addOrder('transaction_time', 'ASC')
            ->addOrder('history_id', 'ASC');

        // Because select by current customer so have no record
        foreach ($transactions as $transaction) {
            switch ($transaction->getTypeOfTransaction()) {
                case Type::SEND_TO_FRIEND:
                    // If the time is expired, add reward points back to customer
                    $oldtime     = strtotime($transaction->getTransactionTime());
                    $currentTime = strtotime($now);
                    $hour        = ($currentTime - $oldtime) / (60 * 60);
                    $hourConfig  = $this->_dataHelper->timeLifeSendRewardPointsToFriend($store->getCode());

                    if ($hourConfig && ($hour > $hourConfig)) {
                        $friendId = $this->_cookieManager->getCookie('friend');
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
                            'transaction_detail'  => $transaction->getData('transaction_detail'),
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

        if (sizeof($_transactions) > 0) {
            foreach ($_transactions as $_transaction) {
                $friendId = $this->_cookieManager->getCookie('friend');
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

        // Set url redirect after logging in
        $mwRedirect = $this->_cookieManager->getCookie('mw_redirect');
        if ($mwRedirect) {
            $this->_customerSession->setBeforeAuthUrl($this->_urlBuilder->getUrl('checkout/cart'));
            $this->_cookieManager->deleteCookie('mw_redirect');
        }
    }
}
