<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\RewardPoints\Model\System\Config\Source\Applyreward;
use MW\RewardPoints\Model\System\Config\Source\Applyrewardtax;
use MW\RewardPoints\Model\Typerule;
use MW\RewardPoints\Model\Statusrule;

class CollectTotalAfter implements ObserverInterface
{
    protected $_arrayRuleActive = [];

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
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_adminOrder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CatalogRulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @var \MW\RewardPoints\Model\CartrulesFactory
     */
    protected $_cartrulesFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Sales\Model\AdminOrder\Create $adminOrder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CatalogRulesFactory $catalogRulesFactory
     * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Sales\Model\AdminOrder\Create $adminOrder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CatalogRulesFactory $catalogRulesFactory,
        \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
    ) {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_sessionManager = $sessionManager;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_currency = $currency;
        $this->_adminOrder = $adminOrder;
        $this->_localeDate = $localeDate;
        $this->_state = $state;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_catalogrulesFactory = $catalogRulesFactory;
        $this->_cartrulesFactory = $cartrulesFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->moduleEnabled())
        {
            $quote = $observer->getQuote();
            $controllerName = $this->_request->getControllerName();
            $actionName     = $this->_request->getActionName();
            $store = $this->_storeManager->getStore();

            if ($controllerName == "multishipping"
                && ($actionName == "overview" || $actionName == "overviewPost")
            ) {
                $shippingAddress = $quote->getAllShippingAddresses();
                $amount = $this->_sessionManager->getMwRewardpointDiscountShowTotal();
                $totalAmountDec = 0;
                $amountPerAddress = 0;
                $max = [];
                $amountResidual = 0;
                $addressToSession = [];
                $earnpointToSession = [];

                if ($amount != 0) {
                    $amountPerAddress = $amount / count($shippingAddress);
                    $maxSubtotal = [];
                    foreach ($shippingAddress as $address) {
                        $maxSubtotal[] = [
                            'address_id' => $address->getAddressId(),
                            'subtotal'   => $address->getSubtotal()
                        ];
                    }
                    $max = $maxSubtotal[0];

                    for ($i = 1; $i < count($maxSubtotal); $i++) {
                        if ($max["subtotal"] < $maxSubtotal[$i]["subtotal"]) {
                            $max = $maxSubtotal[$i];
                        }
                    }

                    foreach ($shippingAddress as $address) {
                        $subTotal = $address->getSubtotal();
                        if ($amountPerAddress > $subTotal) {
                            $lastMemory        = $amountPerAddress - $subTotal;
                            $amountThisAddress = $subTotal;
                            $amountPerAddress  += $lastMemory;
                        } else {
                            $amountThisAddress = $amountPerAddress;
                        }
                        $totalAmountDec += $amountThisAddress;
                    }
                    /** This value will be added to address have the highest subtotal */
                    $amountResidual   = $amount - $totalAmountDec;
                    $totalAmountDec   = 0;
                    $amountPerAddress = $amount / count($shippingAddress);
                    $addressToSession = [];
                }

                $programs = $this->getEarnProgramResult();
                foreach ($shippingAddress as $address) {
                    // For earn point
                    $applyReward          = (int) $this->_dataHelper->getApplyRewardPoints($store->getCode());
                    $applyRewardPointsTax = (int) $this->_dataHelper->getApplyRewardPointsTax($store->getCode());
                    if ($applyReward == Applyreward::BEFORE) {
                        if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                            $baseSubtotalWithDiscount = $address->getBaseSubtotal();
                        } else {
                            $baseSubtotalWithDiscount = $address->getSubtotalInclTax();
                        }
                    } else {
                        if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                            $baseSubtotalWithDiscount = $address->getBaseSubtotal() + $address->getBaseDiscountAmount();
                        } else {
                            $baseSubtotalWithDiscount = $address->getSubtotalInclTax() + $address->getBaseDiscountAmount();
                        }
                    }

                    // Earn point
                    $earnRewardPoint     = 0;
                    $earnRewardPointCart = 0;

                    $productSellPoint  = 0;
                    $rewardPointDetail = [];
                    $items             = $address->getAllVisibleItems();

                    foreach ($items as $item) {
                        $productId = $item->getProductId();
                        $qty       = $item->getQty();

                        $mwRewardPointSell = $this->_productFactory->create()->load($productId)->getMwRewardPointSellProduct();
                        if ($mwRewardPointSell > 0) {
                            $productSellPoint = $productSellPoint + $qty * $mwRewardPointSell;
                        }

                        if ($applyReward == Applyreward::BEFORE) {
                            if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                                $price = $this->_checkoutHelper->getPriceInclTax($item);
                            } else {
                                $price = $item->getBasePrice();
                            }
                        } else {
                            if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                                $price = $this->_checkoutHelper->getPriceInclTax($item) - $item->getBaseDiscountAmount() / $qty;
                            } else {
                                $price = $item->getBasePrice() - $item->getBaseDiscountAmount() / $qty;
                            }
                        }

                        $mwRewardPoint = $qty * $this->_catalogrulesFactory->create()->getPointCatalogRule($productId);
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {

                        } else {
                            $rewardPointArray = $this->processRule($item, $programs, $qty, $price, $baseSubtotalWithDiscount);
                            $rewardPoint      = $rewardPointArray[1];
                            $ruleDetails      = $rewardPointArray[2];

                            foreach ($ruleDetails as $key => $ruleDetail) {
                                if (!isset ($rewardPointDetail[$key])) {
                                    $rewardPointDetail[$key] = 0;
                                }
                                $rewardPointDetail[$key] = $rewardPointDetail[$key] + $ruleDetail;
                            }
                            $earnRewardPoint     = $earnRewardPoint + $mwRewardPoint;
                            $earnRewardPointCart = $rewardPoint;
                        }
                    }

                    $earnPointTotal = $earnRewardPoint + $earnRewardPointCart;
                    if ($earnPointTotal > 0) {
                        $address->addTotal(
                            [
                                'code'   => "earn_points",
                                'title'  => __('You Earn'),
                                'value'  => "NO_FORMAT",
                                'text'   => $earnPointTotal,
                                'strong' => false
                            ],
                            'subtotal'
                        );
                    }

                    $earnpointToSession[$address->getAddressId()] = [
                        'address_id' => $address->getAddressId(),
                        'earnpoints' => $earnPointTotal,
                    ];
                    // End earn point

                    if ($amount != 0) {
                        $subTotal            = $address->getSubtotal();
                        if ($amountPerAddress > $subTotal) {
                            $lastMemory        = $amountPerAddress - $subTotal;
                            $amountThisAddress = $subTotal;
                            $amountPerAddress  += $lastMemory;
                        } else {
                            $amountThisAddress = $amountPerAddress;
                        }

                        if ($max['address_id'] == $address->getAddressId()) {
                            $amountThisAddress = $amountResidual + $amountThisAddress;
                        }

                        /** [start] re-collect for each subtotal per address */
                        /** at the moment, then no need, no support */
                        /** [end] re-collect */

                        $pointShow = $this->_dataHelper->exchangeMoneysToPoints($amountThisAddress, $store->getCode());
                        $address->addTotal(
                            [
                                'code'   => "reward_points",
                                'title'  => __('You Redeem (%1)', $pointShow),
                                'value'  => -$amountThisAddress,
                                'strong' => false
                            ]
                        );

                        $totalAmountDec += $amountThisAddress;
                        $addressToSession[$address->getAddressId()] = [
                            'address_id'            => $address->getAddressId(),
                            'rewardpoints'          => $pointShow,
                            'rewardpoints_discount' => $amountThisAddress,
                        ];
                        $address->setMwRewardpoint($pointShow)
                            ->setMwRewardpointDiscountShow($amountThisAddress)
                            ->setMwRewardpointDiscount($amountThisAddress)
                            ->setGrandTotal((float)$address->getGrandTotal() - $amountThisAddress)
                            ->setBaseGrandTotal((float)$address->getBaseGrandTotal() - $amountThisAddress);
                    }
                }

                if ($actionName == "overview") {
                    if ($amount != 0) {
                        $this->_sessionManager->setQuoteAddressRewardpoint($addressToSession);
                    }

                    $this->_sessionManager->setQuoteAddressEarnpoint($earnpointToSession);
                }

                if ($amount != 0) {
                    $quote->setGrandTotal((float)$quote->getGrandTotal() - $totalAmountDec)
                        ->setBaseGrandTotal((float)$quote->getBaseGrandTotal() - $totalAmountDec)
                        ->save();
                }
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param $programs
     * @param $qty
     * @param $price
     * @param $baseSubtotalWithDiscount
     * @return array
     */
    public function processRule(
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $programs,
        $qty,
        $price,
        $baseSubtotalWithDiscount
    ) {
        $result            = [];
        $resultRewardPoint = 0;
        $resultDetail      = [];
        $programRule       = [];
        $store = $this->_storeManager->getStore();
        $baseCurrencyCode = $store->getBaseCurrencyCode();
        $currentCurrencyCode = $store->getCurrentCurrencyCode();
        // Allowed currencies
        $allowedCurrencies = $this->_currency->getConfigAllowCurrencies();
        $rates = $this->_currency->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        // The price converted
        $price = $price / $rates[$currentCurrencyCode];
        $baseSubtotalWithDiscount = $baseSubtotalWithDiscount / $rates[$currentCurrencyCode];

        foreach ($programs as $program) {
            $programRule[]   = $program;
            $rule            = $this->_cartrulesFactory->create()->load($program);
            $rewardPoint     = (int) $rule->getRewardPoint();
            $simpleAction    = (int) $rule->getSimpleAction();
            $rewardStep      = (int) $rule->getRewardStep();
            $stopRule        = (int) $rule->getStopRulesProcessing();
            $rule->afterLoad();
            $address = $this->getAddress($item);

            if (($rule->validate($address)) && ($rule->getActions()->validate($item))) {
                switch ($simpleAction) {
                    case Typerule::FIXED:
                        $resultRewardPoint += $rewardPoint;
                        if (!isset($resultDetail[$program])) {
                            $resultDetail[$program] = 0;
                        }
                        $resultDetail[$program] += $rewardPoint;
                        break;
                    case Typerule::FIXED_WHOLE_CART:
                        $resultRewardPoint += $rewardPoint;
                        if (!isset($resultDetail[$program])) {
                            $resultDetail[$program] = 0;
                        }
                        $resultDetail[$program] += $rewardPoint;
                        break;
                    case Typerule::BUY_X_GET_Y_WHOLE_CART:
                        if ($rewardStep > 0) {
                            $resultRewardPoint += (int)($baseSubtotalWithDiscount / $rewardStep) * $rewardPoint;
                            if (!isset ($resultDetail[$program])) {
                                $resultDetail[$program] = 0;
                            }
                            $resultDetail[$program] += (int)($baseSubtotalWithDiscount / $rewardStep) * $rewardPoint;
                        }
                        break;
                    default :
                        if ($rewardStep > 0) {
                            $resultRewardPoint += (int)(($qty * $price) / $rewardStep) * $rewardPoint;
                            if (!isset ($resultDetail[$program])) {
                                $resultDetail[$program] = 0;
                            }
                            $resultDetail[$program] += (int)(($qty * $price) / $rewardStep) * $rewardPoint;
                        }
                }

                if (!in_array($program, $this->_arrayRuleActive)) {
                    $this->_arrayRuleActive[] = $program;
                }

                if ($stopRule) {
                    foreach (array_diff($programs, $programRule) as $programRuleValue) {
                        if (!in_array($programRuleValue, $this->_arrayRuleActive)) {
                            $this->_arrayRuleActive[] = $programRuleValue;
                        }
                    }
                    break;
                }
            }
        }

        $result[1] = $resultRewardPoint;
        $result[2] = $resultDetail;

        return $result;
    }

    /**
     * @return array
     */
    protected function getEarnProgramResult()
    {
        $programIds = [];
        // Check programs by enable
        $collection = $this->_cartrulesFactory->create()->getCollection()
            ->addFieldToFilter('status', ['eq' => Statusrule::ENABLED]);

        if ($collection->count() > 0) {
            $position = [];

            // Get current customer group ID
            if ($this->_state->getAreaCode() == 'adminhtml') {
                $quote = $this->_adminOrder->getQuote();
                if (!is_null($quote)) {
                    $customerGroupId = $quote->getCustomerGroupId();
                } else {
                    $customerGroupId = $this->_customerSession->getCustomerGroupId();
                }
            } else {
                $customerGroupId = $this->_customerSession->getCustomerGroupId();
            }

            // Get current store ID
            $storeId = $this->_storeManager->getStore()->getId();

            foreach ($collection as $program) {
                // Check programs by time
                $startDate = $program->getStartDate();
                $endDate   = $program->getEndDate();

                if ($this->_localeDate->isScopeDateInInterval(null, $startDate, $endDate)) {
                    // Check programs by customer group
                    $customerGroupIds = explode(',', $program->getCustomerGroupIds());

                    if (in_array($customerGroupId, $customerGroupIds)) {
                        // Check programs by store view
                        $storeViews = explode(',', $program->getStoreView());

                        if (in_array($storeId, $storeViews) || $storeViews[0] == '0') {
                            // Push program ID which is valid to array
                            $programIds[] = $program->getRuleId();
                            // Get positions
                            $position[]   = (int) $program->getRulePosition();
                        }
                    }
                }
            }

            // Sort program by position
            if (sizeof($programIds) > 0) {
                array_multisort($position, $programIds);
            }
        }

        return $programIds;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function getAddress(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Address\Item) {
            $address = $item->getAddress();
        } elseif ($item->getQuote()->isVirtual()) {
            $address = $item->getQuote()->getBillingAddress();
        } else {
            $address = $item->getQuote()->getShippingAddress();
        }

        return $address;
    }
}
