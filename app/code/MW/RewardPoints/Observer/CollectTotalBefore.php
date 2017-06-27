<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class CollectTotalBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory
    ) {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_request->getControllerName() == 'multishipping') {
            return true;
        }

        $quote = $observer->getQuote();

        if ($this->_dataHelper->moduleEnabled()) {
            $store = $this->_storeManager->getStore();
            $spendPoint     = $quote->getSpendRewardpointCart();
            $mwRewardpoints = (int) $quote->getMwRewardpoint();
            $min            = (int) $this->_dataHelper->getMinPointCheckoutStore($store->getCode());

            if ($min > 0 && $mwRewardpoints < $min) {
                $quote->setMwRewardpoint(0);
                $quote->setMwRewardpointDiscount(0)->save();
            }

            $maxPointsDiscount = $this->_dataHelper->exchangePointsToMoneys($spendPoint, $store->getCode());
            if ($maxPointsDiscount < 0) {
                $maxPointsDiscount = 0;
            }

            $rewardpointDiscount = (double) $quote->getMwRewardpointDiscount();
            $baseGrandTotalAfterRewardpoint = $quote->getBaseGrandTotal() + $rewardpointDiscount;

            if ($rewardpointDiscount > $baseGrandTotalAfterRewardpoint) {
                $points = $this->_dataHelper->exchangeMoneysToPoints(
                    $baseGrandTotalAfterRewardpoint,
                    $store->getCode()
                );
                $quote->setMwRewardpointDiscount($baseGrandTotalAfterRewardpoint);
                $quote->setMwRewardpoint($this->_dataHelper->roundPoints($points, $store->getCode()))->save();
            }

            if ($rewardpointDiscount > $maxPointsDiscount) {
                $quote->setMwRewardpointDiscount($maxPointsDiscount);
                $quote->setMwRewardpoint($this->_dataHelper->roundPoints($spendPoint, $store->getCode()))->save();
                if ($maxPointsDiscount > $baseGrandTotalAfterRewardpoint) {
                    $points = $this->_dataHelper->exchangeMoneysToPoints(
                        $baseGrandTotalAfterRewardpoint,
                        $store->getCode()
                    );
                    $quote->setMwRewardpointDiscount($baseGrandTotalAfterRewardpoint);
                    $quote->setMwRewardpoint($this->_dataHelper->roundPoints($points, $store->getCode()))->save();
                }
            }

            if ($quote->getCustomerId()) {
                $productSellPoint  = 0;

                foreach ($quote->getAllItems() as $item) {
                    $qty     = $item->getQty();
                    $product = $this->_productFactory->create()->load($item->getProductId());

                    switch ($product->getTypeId()) {
                        case 'simple':
                        case 'virtual':
                        case 'downloadable':
                            $mwRewardPointSellProduct = $product->getData('mw_reward_point_sell_product');
                            if ($mwRewardPointSellProduct > 0) {
                                $productSellPoint = $productSellPoint + $qty * $mwRewardPointSellProduct;
                            }
                            break;
                        case 'bundle':
                            $mwRewardPointSellProduct = $product->getData('mw_reward_point_sell_product');
                            if ($mwRewardPointSellProduct > 0) {
                                $productSellPoint = $productSellPoint + $qty * $mwRewardPointSellProduct;
                            } else {
                                foreach ($item->getChildren() as $bundleItem) {
                                    $childProduct = $this->_productFactory->create()->load($bundleItem->getProductId());
                                    $childPointSellProduct = $childProduct->getData('mw_reward_point_sell_product');

                                    if ($childPointSellProduct > 0) {
                                        $productSellPoint = $productSellPoint + $bundleItem->getQty() * $childPointSellProduct;
                                    }
                                }
                            }
                            break;
                    }
                }

                $customerRewarpoint = $this->_memberFactory->create()->load($quote->getCustomerId())->getMwRewardPoint();
                if ($productSellPoint > 0 && $customerRewarpoint < $productSellPoint + $quote->getMwRewardpoint()) {
                    $quote->setMwRewardpointDiscount(0)
                        ->setMwRewardpoint(0)
                        ->save();
                    $this->_messageManager->getMessages(true);
                    $this->_checkoutSession->setAllowCheckout(false);
                    $quote->setHasError(true);
                    $this->_messageManager->addError(__('You do not have enough points for product in cart.'));
                } else {
                    $this->_checkoutSession->setAllowCheckout(true);
                }
            } else {
                if (!$this->_customerSession->isLoggedIn()) {
                    $this->_messageManager->getMessages(true);
                    $this->_messageManager->addError(__('For using points to checkout order, please login!'));
                }
            }
        } else {
            $quote->setMwRewardpointDiscount(0)
                ->setMwRewardpoint(0)
                ->save();
        }

        return $this;
    }
}
