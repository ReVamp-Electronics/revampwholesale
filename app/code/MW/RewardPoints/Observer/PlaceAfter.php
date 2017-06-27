<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Type\Onepage;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class PlaceAfter implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointsorderFactory
     */
    protected $_rwpOrderFactory;

    /**
     * @var \MW\RewardPoints\Model\CatalogrulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @var \MW\RewardPoints\Model\CartrulesFactory
     */
    protected $_cartrulesFactory;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \MW\RewardPoints\Observer\OrderSaveAfter
     */
    protected $_orderObserver;

    /**
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Observer\OrderSaveAfter $orderObserver
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
        \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Observer\OrderSaveAfter $orderObserver
    ) {
        $this->_storeFactory = $storeFactory;
        $this->_quote = $quote;
        $this->_customerFactory = $customerFactory;
        $this->_productFactory = $productFactory;
        $this->_orderFactory = $orderFactory;
        $this->_cookieManager = $cookieManager;
        $this->_sessionManager = $sessionManager;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
        $this->_rwpOrderFactory = $rwpOrderFactory;
        $this->_catalogrulesFactory = $catalogrulesFactory;
        $this->_cartrulesFactory = $cartrulesFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_orderObserver = $orderObserver;
    }

    /**
     * Add transaction and calculate reward points after place order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->moduleEnabled()) {
            $order = $observer->getEvent()->getOrder();
            $orders = $observer->getEvent()->getOrders();
            $quote = $observer->getEvent()->getQuote();

            if (empty($order)) {
                // Is multi shipping
                if (count($orders) > 0) {
                    foreach ($orders as $order) {
                        $this->processOrder($quote, $order, true);
                        $this->addPointsForPendingOrder($observer, $order);
                    }
                }
            } else {
                $this->processOrder($quote, $order);
                $this->addPointsForPendingOrder($observer, $order);
            }
        }
    }

    /**
     * Process per Order
     *
     * @param  $quote
     * @param  $order
     * @param  $multi
     * @return void
     */
    public function processOrder($quote, $order, $multi = false)
    {
        $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
        $store = $this->_storeFactory->create()->load($order->getStoreId());

        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            $quote = $this->_quote->setSharedStoreIds([$order->getStoreId()])
                ->load($order->getQuoteId());
        }

        if ($quote->getCheckoutMethod(true) == Onepage::METHOD_REGISTER) {
            $this->_memberFactory->create()->customerSaveAfterRegister($order);
        }

        $customer = $this->_customerFactory->create()->load($order->getCustomerId());
        if ($customer->getId()) {
            $this->_dataHelper->checkAndInsertCustomerId($customer->getId(), 0);
            $_customer = $this->_memberFactory->create()->load($customer->getId());

            if ($multi) {
                $addressRewardpoints = $this->_sessionManager->getQuoteAddressRewardpoint();
                $addressEarnpoints   = $this->_sessionManager->getQuoteAddressEarnpoint();
                $rewardpoints        = (int)$addressRewardpoints[$order->getShippingAddress()->getQuoteAddressId()]["rewardpoints"];
                $earnRewardpoint     = (int)$addressEarnpoints[$order->getShippingAddress()->getQuoteAddressId()]["earnpoints"];
                $money               = $addressRewardpoints[$order->getShippingAddress()->getQuoteAddressId()]["rewardpoints_discount"];
            } else {
                $earnRewardpoint = (int)$quote->getEarnRewardpoint();
                $rewardpoints    = (int)$quote->getMwRewardpoint();
                $money           = (float) $quote->getMwRewardpointDiscount();
            }

            // Subtract reward points of customer and save reward points to order
            // if customer use this point to checkout
            $rewardpointsNew = $rewardpoints + (int)$quote->getMwRewardpointSellProduct();
            if ($rewardpointsNew || $earnRewardpoint) {
                if ($rewardpointsNew) {
                    $expiredDay = (int) $this->_dataHelper->getExpirationDaysPoint($store->getCode());
                    // Subtract reward points of customer
                    $_customer->addRewardPoint(-$rewardpointsNew);
                    $historyData = [
                        'type_of_transaction' => Type::USE_TO_CHECKOUT,
                        'amount' => (int) $rewardpointsNew,
                        'balance' => $_customer->getMwRewardPoint(),
                        'transaction_detail' => $order->getIncrementId(),
                        'transaction_time' => $now,
                        'expired_day' => $expiredDay,
                        'expired_time' => null,
                        'point_remaining' => 0,
                        'history_order_id' => $order->getId(),
                        'status' => Status::COMPLETE
                    ];

                    $_customer->saveTransactionHistory($historyData);

                    // Send email notification
                    $this->_dataHelper->sendEmailCustomerPointChanged(
                        $_customer->getId(),
                        $historyData,
                        $store->getCode()
                    );

                    $this->_dataHelper->processExpiredPointsWhenSpentPoints(
                        $_customer->getId(),
                        $rewardpointsNew
                    );
                }

                // Save reward point for order
                $orderData = [
                    'order_id' => $order->getId(),
                    'reward_point' => $rewardpoints,
                    'rewardpoint_sell_product' => (int) $quote->getMwRewardpointSellProduct(),
                    'earn_rewardpoint' => $earnRewardpoint,
                    'money' => $money,
                    'reward_point_money_rate' => $this->_dataHelper->getPointMoneyRateConfig($store->getCode())
                ];
                $_order = $this->_rwpOrderFactory->create();
                $_order->getResource()->saveRewardOrder($orderData);
            }

            if ($earnRewardpoint > 0) {
                $detail = [];
                $detailRuleResult = [];
                $detailProducts = [];
                $detailRules = unserialize($quote->getMwRewardpointDetail());

                foreach ($detailRules as $key => $detailRule) {
                    if ($detailRule > 0) {
                        $ruleDescription = $this->_cartrulesFactory->create()->load($key)->getDescription();
                        $detailRuleResult[] = __('%1 | %2', $detailRule, $ruleDescription);
                    }
                }

                foreach ($quote->getAllVisibleItems() as $item) {
                    $productId = $item->getProduct()->getId();
                    $mwRewardPoint = $this->_catalogrulesFactory->create()->getPointCatalogRule($productId);
                    if ($mwRewardPoint > 0) {
                        $productName = $this->_productFactory->create()->load($productId)->getName();
                        $detailProducts[] = __('%1 | %2', $mwRewardPoint * $item->getQty(), $productName);
                    }
                }
                $detail[1] = $detailRuleResult;
                $detail[2] = $detailProducts;

                $results = $this->_dataHelper->getTransactionExpiredPoints($earnRewardpoint,$store->getCode());
                $expiredDay      = $results[0];
                $expiredTime     = $results[1] ;
                $remainingPoints = $results[2];

                $historyData = [
                    'type_of_transaction' => Type::CHECKOUT_ORDER_NEW,
                    'amount' => $earnRewardpoint,
                    'balance' => $_customer->getMwRewardPoint(),
                    'transaction_detail' => $order->getIncrementId()."||".serialize($detail),
                    'transaction_time' => $now,
                    'expired_day' => $expiredDay,
                    'expired_time' => $expiredTime,
                    'point_remaining' => $remainingPoints,
                    'history_order_id' => $order->getId(),
                    'status' => Status::PENDING
                ];
                $_customer->saveTransactionHistory($historyData);
            }

            // Reward points to friend if this is first purchase
            $orders = $this->_orderFactory->create()->getCollection()
                ->addFieldToFilter('customer_id',$customer->getId());
            if ($_customer->getMwFriendId()) {
                $this->_dataHelper->checkAndInsertCustomerId($_customer->getMwFriendId(), 0);
            }

            $friend = $_customer->getFriend();
            if ((sizeof($orders) == 1)
                && $friend
                && $this->_dataHelper->checkCustomerMaxBalance($friend->getCustomerId(), $store->getCode())
            ) {
                $customerGroupId = $this->_customerFactory->create()->load($customer->getId())->getGroupId();
                $results = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                    Type::FRIEND_FIRST_PURCHASE,
                    $customerGroupId,
                    $store->getId()
                );
                $point           = explode('/',$results[0]);
                $expiredDay      = $results[1];
                $expiredTime     = $results[2];
                $remainingPoints = $results[3];
                $_point          = $point[0];

                if (sizeof($point) == 2) {
                    $total = $order->getBaseGrandTotal();
                    $_point = ((int)($total / $point[1])) * $point[0];
                }

                if ($_point) {
                    $historyData = [
                        'type_of_transaction' => Type::FRIEND_FIRST_PURCHASE,
                        'amount' => (int) $_point,
                        'balance' => $friend->getMwRewardPoint(),
                        'transaction_detail' => $customer->getId()."|".$order->getId(),
                        'transaction_time' => $now,
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoints,
                        'history_order_id' => $order->getId(),
                        'status' => Status::PENDING
                    ];
                    $friend->saveTransactionHistory($historyData);
                }
            } elseif ($friend
                && $this->_dataHelper->checkCustomerMaxBalance($friend->getCustomerId(), $store->getCode())
            ) {
                // Reward points to friend if this is next purchase
                $customerGroupId = $this->_customerFactory->create()->load($customer->getId())->getGroupId();
                $results = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                    Type::FRIEND_NEXT_PURCHASE,
                    $customerGroupId,
                    $store->getId()
                );
                $point           = explode('/',$results[0]);
                $expiredDay      = $results[1];
                $expiredTime     = $results[2];
                $remainingPoints = $results[3];
                $_point = $point[0];
                if (sizeof($point) == 2) {
                    $total = $order->getBaseGrandTotal();
                    $_point = ((int)($total / $point[1])) * $point[0];
                }

                if ($_point) {
                    $historyData = [
                        'type_of_transaction' => Type::FRIEND_NEXT_PURCHASE,
                        'amount' => (int)$_point,
                        'balance' => $friend->getMwRewardPoint(),
                        'transaction_detail' => $customer->getId() . "|" . $order->getId(),
                        'transaction_time' => $now,
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoints,
                        'history_order_id' => $order->getId(),
                        'status' => Status::PENDING
                    ];
                    $friend->saveTransactionHistory($historyData);
                }
            }
        } else {
            $friendId = $this->_cookieManager->getCookie('friend');
            $friend = $this->_memberFactory->create()->load($friendId);

            if ($friend) {
                $results         = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                    Type::FRIEND_FIRST_PURCHASE,
                    0,
                    $store->getId()
                );
                $point           = explode('/', $results[0]);
                $expiredDay      = $results[1];
                $expiredTime     = $results[2];
                $remainingPoints = $results[3];
                $_point          = $point[0];
                if (sizeof($point) == 2) {
                    $total  = $order->getBaseGrandTotal();
                    $_point = ((int)($total / $point[1])) * $point[0];
                }

                if ($_point) {
                    $historyData = [
                        'type_of_transaction' => Type::FRIEND_FIRST_PURCHASE,
                        'amount'              => (int)$_point,
                        'balance'             => $friend->getMwRewardPoint() + (int)$_point,
                        'transaction_detail'  => "Guest|" . $order->getId(),
                        'transaction_time'    => $now,
                        'expired_day'         => $expiredDay,
                        'expired_time'        => $expiredTime,
                        'point_remaining'     => $remainingPoints,
                        'history_order_id'    => $order->getId(),
                        'status'              => Status::COMPLETE
                    ];
                    $friend->saveTransactionHistory($historyData);
                    $friend->addRewardPoint((int)$_point);
                }
            }
        }
    }

    /**
     * Add points for pending order
     *
     * @param $observer
     * @param $order
     */
    public function addPointsForPendingOrder($observer, $order)
    {
        $store = $this->_storeFactory->create()->load($order->getStoreId());
        $statusAddRewardPoint = $this->_dataHelper->getStatusAddRewardPointStore($store->getCode());

        if ($order->getStatus() == $statusAddRewardPoint) {
            $this->_orderObserver->completeOrder($observer);
        }
    }
}
