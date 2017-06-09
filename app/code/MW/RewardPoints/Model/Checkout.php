<?php

namespace MW\RewardPoints\Model;

use Magento\Checkout\Model\Type\Onepage;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Checkout
{
    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

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
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointsorderFactory
     */
    protected $_rwpOrderFactory;

    /**
     * @var \MW\RewardPoints\Model\ProductpointFactory
     */
    protected $_productpointFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory
     * @param \MW\RewardPoints\Model\ProductpointFactory $productpointFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Quote\Model\Quote $quote
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface
     */
    public function __construct(
        \MW\RewardPoints\Helper\Data $dataHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory,
        \MW\RewardPoints\Model\ProductpointFactory $productpointFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Quote\Model\Quote $quote,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
        \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_customerFactory = $customerFactory;
        $this->_memberFactory = $memberFactory;
        $this->_rwpOrderFactory = $rwpOrderFactory;
        $this->_productpointFactory = $productpointFactory;
        $this->_productFactory = $productFactory;
        $this->_orderFactory = $orderFactory;
        $this->_historyFactory = $historyFactory;
        $this->_storeFactory = $storeFactory;
        $this->_cookieManager = $cookieManager;
        $this->_quote = $quote;
        $this->_catalogrulesFactory = $catalogrulesFactory;
        $this->_cartrulesFactory = $cartrulesFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function processOrderCreationData(\Magento\Framework\Event\Observer $observer)
    {
        $model   = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote   = $model->getQuote();
        if (isset($request['mw_rewardpoint_add']) && isset($request['customer_id'])) {
            $rewardpoints = (int)$request['mw_rewardpoint_add'];
            $customerId  = $request['customer_id'];

            if (isset($request['store_id'])) {
                $storeId = $request['store_id'];
            } else {
                $storeId = $this->_storeManager->getStore()->getId();
            }
            $store = $this->_storeFactory->create()->load($storeId);

            try {
                if ($rewardpoints < 0) {
                    $rewardpoints = -$rewardpoints;
                }

                if ($rewardpoints >= 0) {
                    $baseGrandTotal = $quote->getBaseGrandTotal();
                    $this->_dataHelper->setPointToCheckOut(
                        $rewardpoints,
                        $quote,
                        $customerId,
                        $store->getCode(),
                        $baseGrandTotal
                    );
                } else {
                    $this->_messageManager->addError(__('Cannot apply Reward Points'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_messageManager->addException($e, __('Cannot apply Reward Points'));
            }
        }

        if (isset($request['mw_rewardpoint_remove'])) {
            try {
                $quote->setMwRewardpoint(0)
                    ->setMwRewardpointDiscount(0)
                    ->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_messageManager->addException($e, __('Cannot remove Reward Points'));
            }
        }

        $quote->collectTotals()->save();

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function salesConvertQuoteAdressToOrderAddress(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->moduleEnabled()) {
            $address      = $observer->getAddress();
            $orderAddress = $observer->getOrderAddress();
            $orderAddress->setQuoteAddressId($address->getAddressId());
        }

        return $this;
    }
}
