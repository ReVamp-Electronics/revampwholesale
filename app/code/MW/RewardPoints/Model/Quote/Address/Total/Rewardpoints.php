<?php

namespace MW\RewardPoints\Model\Quote\Address\Total;

use MW\RewardPoints\Model\System\Config\Source\Redeemtax;
use MW\RewardPoints\Model\System\Config\Source\Applyreward;
use MW\RewardPoints\Model\System\Config\Source\Applyrewardtax;
use MW\RewardPoints\Model\Typerule;
use MW\RewardPoints\Model\Typerulespend;
use MW\RewardPoints\Model\Statusrule;

class Rewardpoints extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_arrayRuleActive;

    protected $_arrayWholeCart;

    protected $_arrayWholeCartXY;

    protected $_rewardPointDetail;

    protected $_earnRewardPoint;

    protected $_earnRewardPointCart;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_adminOrder;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\ProductsellpointFactory
     */
    protected $_productsellpointFactory;

    /**
     * @var \MW\RewardPoints\Model\CatalogrulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @var \MW\RewardPoints\Model\SpendcartrulesFactory
     */
    protected $_spendcartrulesFactory;

    /**
     * @var \MW\RewardPoints\Model\CartrulesFactory
     */
    protected $_cartrulesFactory;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Sales\Model\AdminOrder\Create $adminOrder
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     * @param \MW\RewardPoints\Model\SpendcartrulesFactory $spendcartrulesFactory
     * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\AdminOrder\Create $adminOrder,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
        \MW\RewardPoints\Model\SpendcartrulesFactory $spendcartrulesFactory,
        \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManger;
        $this->_request = $request;
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionManager = $sessionManager;
        $this->_state = $state;
        $this->_customerSession = $customerSession;
        $this->_localeDate = $localeDate;
        $this->_adminOrder = $adminOrder;
        $this->_dataHelper = $dataHelper;
        $this->_productsellpointFactory = $productsellpointFactory;
        $this->_catalogrulesFactory = $catalogrulesFactory;
        $this->_spendcartrulesFactory = $spendcartrulesFactory;
        $this->_cartrulesFactory = $cartrulesFactory;
        $this->setCode('reward_points');
    }

    /**
     * This function will clean values when the totals are called many times
     */
    public function cleanValues()
    {
        $this->_arrayRuleActive     = [];
        $this->_arrayWholeCart      = [];
        $this->_arrayWholeCartXY    = [];
        $this->_rewardPointDetail   = [];
        $this->_earnRewardPoint     = 0;
        $this->_earnRewardPointCart = 0;
    }

    /**
     * Collect reward points and redeem points
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if ($this->_request->getControllerName() == 'multishipping') {
            return $this;
        }

        $this->cleanValues();
        $store    = $this->_storeManager->getStore();
        $address  = $shippingAssignment->getShipping()->getAddress();
        $items    = $address->getAllVisibleItems();
        $tax      = 0;
        $shipping = 0;

        if ((int)$this->_dataHelper->getRedeemPointsOnTax($store->getCode()) == Redeemtax::BEFORE) {
            // Collect reward points when using the configuration: Calculate to TAX - Before Redeempoint
            $this->collectWhenCalculateToTaxBeforeRedeemPoints($address, $store, $quote, $items, $tax, $shipping, $total);
        } else if ((int)$this->_dataHelper->getRedeemPointsOnTax($store->getCode()) == Redeemtax::AFTER) {
            // Collect reward points when using the configuration: Calculate to TAX - After Redeempoint
            $this->collectWhenCalculateToTaxAfterRedeemPoints($address, $store, $quote, $items, $tax, $shipping, $total);
        }

        return $this;
    }

    /**
     * Collect reward points when using the configuration: Calculate to TAX - Before Redeempoint
     *
     * @param $address
     * @param $store
     * @param $quote
     * @param $items
     * @param $tax
     * @param $shipping
     * @param $total
     * @return $this
     */
    public function collectWhenCalculateToTaxBeforeRedeemPoints($address, $store, $quote, $items, $tax, $shipping, $total)
    {
        $newTax        = 0;
        $newShipping   = 0;
        $checkTax      = $this->_dataHelper->getRedeemedTaxConfig($store->getCode());
        $checkShipping = $this->_dataHelper->getRedeemedShippingConfig($store->getCode());

        if ($checkTax) {
            $tax = $total->getBaseTaxAmount();
        } else {
            $newTax = $total->getBaseTaxAmount();
        }

        if ($checkShipping) {
            $shipping = $total->getBaseShippingInclTax();
        } else {
            $newShipping = $total->getBaseShippingInclTax();
        }

        // Function check reward admin
        $customerId = $quote->getCustomerId();
        if ($customerId) {
            $baseGrandTotal = $total->getBaseGrandTotal() - $newTax - $newShipping;

            if ($quote->getMwRewardpointDiscount() > $baseGrandTotal && $baseGrandTotal > 0) {
                $quote->setMwRewardpointDiscount($baseGrandTotal);
                $points = $this->_dataHelper->exchangeMoneysToPoints($baseGrandTotal, $store->getCode());
                $quote->setMwRewardpoint($this->_dataHelper->roundPoints($points, $store->getCode()));
            }
        }

        if (!count($items)) {
            return $this;
        }

        $applyReward          = (int) $this->_dataHelper->getApplyRewardPoints($store->getCode());
        $applyRewardPointsTax = (int) $this->_dataHelper->getApplyRewardPointsTax($store->getCode());
        // Calculate Reward Points Earned configuration
        if ($applyReward == Applyreward::BEFORE) {
            // Before Discount
            if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                // Before Tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotal();
            } else {
                // After Tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotalInclTax();
            }
        } else {
            // After Discount
            if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                // Before Tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotal() + $total->getBaseDiscountAmount();
            } else {
                // After Tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotal() + $total->getBaseDiscountAmount() + $total->getBaseTaxAmount();
            }
        }

        // Get programs for spending point
        $spendSubPoint  = 0;
        $spendSubMoney  = 0;
        $spendPrograms  = $this->getSpendProgramResult();

        // Get programs for earning point
        $programs         = $this->getEarnProgramResult();
        $productSellPoint = 0;

        // Calculate earning points and sending points for each item and child item (bundle, configurable)
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $qty       = $item->getQty();

            $product = $this->_productFactory->create()->load($productId);
            // Get points of catalog rule
            $mwRewardPoint = $qty * $this->_catalogrulesFactory->create()->getPointCatalogRule($productId);

            // Sell points
            switch ($product->getTypeId()) {
                case 'simple':
                case 'virtual':
                case 'downloadable':
                    $mwRewardPointSell = $product->getMwRewardPointSellProduct();
                    if ($mwRewardPointSell > 0) {
                        $productSellPoint += $qty * $mwRewardPointSell;
                    }
                    break;
                case 'bundle':
                    $mwRewardPointSell = $product->getMwRewardPointSellProduct();
                    if ($mwRewardPointSell > 0) {
                        $productSellPoint += $qty * $mwRewardPointSell;
                    } else {
                        foreach ($item->getChildren() as $bundleItem) {
                            $childProduct = $this->_productFactory->create()->load($bundleItem->getProductId());
                            $childPointSell = $childProduct->getMwRewardPointSellProduct();

                            if ($childPointSell > 0) {
                                $productSellPoint = $productSellPoint + $bundleItem->getQty() * $childPointSell;
                            }
                        }
                    }

                    break;
                case 'configurable':
                    // Total points = parent points + all children points
                    $mwRewardPointSell = $product->getMwRewardPointSellProduct();
                    if ($mwRewardPointSell > 0) {
                        $productSellPoint += $qty * $mwRewardPointSell;
                    } else {
                        if ($info = $item->getProduct()->getCustomOption('info_buyRequest')) {
                            $infoArr = unserialize($info->getValue());

                            foreach ($infoArr['super_attribute'] as $attributeId => $value) {
                                $collection = $this->_productsellpointFactory->create()->getCollection()
                                    ->addFieldToFilter('product_id', $product->getId())
                                    ->addFieldToFilter('option_id', $value)
                                    ->addFieldToFilter('option_type_id', $attributeId)
                                    ->addFieldToFilter('type_id', 'super_attribute')
                                    ->getFirstItem();

                                $productSellPoint += intval($collection->getSellPoint());
                            }
                        }
                    }
                    break;
            }

            $priceWithDiscount = $item->getBasePrice() - $item->getBaseDiscountAmount() / $qty;
            if ($applyReward == Applyreward::BEFORE) {
                if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                    $price = $item->getBasePrice();
                } else {
                    $price = $item->getBasePriceInclTax();
                }
            } else {
                if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                    $price = $priceWithDiscount;
                } else {
                    $itemDiscountTaxAmount = ($item->getBaseDiscountAmount() * $item->getTaxPercent() / 100) / $qty;
                    $price = $item->getBasePriceInclTax() - $item->getBaseDiscountAmount() / $qty - $itemDiscountTaxAmount;
                }
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $mwRewardPointBundle = 0;

                foreach ($item->getChildren() as $child) {
                    $qtyChild = $child->getQty();
                    $childPriceWithDiscount = $child->getBasePrice() - $child->getBaseDiscountAmount() / $qtyChild;

                    if ($applyReward == Applyreward::BEFORE) {
                        if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                            $childPrice = $child->getBasePrice();
                        } else {
                            $childPrice = $child->getBasePriceInclTax();
                        }
                    } else {
                        if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                            $childPrice = $childPriceWithDiscount;
                        } else {
                            $childDiscountTaxAmount = ($child->getBaseDiscountAmount() * $child->getTaxPercent() / 100) / $qtyChild;
                            $childPrice = $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() / $qtyChild - $childDiscountTaxAmount;
                        }
                    }

                    $arraySpendRewardPoint = $this->spendProcessRule(
                        $child,
                        $spendPrograms,
                        $qtyChild,
                        $childPriceWithDiscount
                    );
                    if ((int)$arraySpendRewardPoint[1] > 0) {
                        $spendSubPoint += $qty * $arraySpendRewardPoint[1];
                    }
                    if ((double)$arraySpendRewardPoint[2] > 0) {
                        $spendSubMoney += $qty * $arraySpendRewardPoint[2];
                    }

                    $rewardPointArray = $this->processRuleConfigurableProduct(
                        $child,
                        $programs,
                        $qtyChild,
                        $childPrice,
                        $qty,
                        $baseSubtotalWithDiscount
                    );
                    $rewardPoint = $rewardPointArray[1];
                    $ruleDetails = $rewardPointArray[2];

                    foreach ($ruleDetails as $key => $ruleDetail) {
                        if (!isset($this->_rewardPointDetail[$key])) {
                            $this->_rewardPointDetail[$key] = 0;
                        }
                        $this->_rewardPointDetail[$key] += $ruleDetail;
                    }

                    if ($product->getTypeId() == 'bundle') {
                        // Check if spend Y get X point
                        $mwRewardPoint = 0;
                        $mwRewardPointBundle += $qtyChild * $this->_catalogrulesFactory->create()->getPointCatalogRule($child->getProductId());
                    }

                    $this->_earnRewardPoint     += $rewardPoint + $mwRewardPoint;
                    $this->_earnRewardPointCart += $rewardPoint;
                }

                if ($product->getTypeId() == 'bundle') {
                    $this->_earnRewardPoint += $mwRewardPointBundle * $qty;
                }
            } else {
                $arraySpendRewardPoint = $this->spendProcessRule(
                    $item,
                    $spendPrograms,
                    $qty,
                    $priceWithDiscount
                );
                if ((int)$arraySpendRewardPoint[1] > 0) {
                    $spendSubPoint += $arraySpendRewardPoint[1];
                }
                if ((double)$arraySpendRewardPoint[2] > 0) {
                    $spendSubMoney += $arraySpendRewardPoint[2];
                }

                $rewardPointArray = $this->processRule(
                    $item,
                    $programs,
                    $qty,
                    $price,
                    $baseSubtotalWithDiscount
                );
                $rewardPoint = $rewardPointArray[1];
                $ruleDetails = $rewardPointArray[2];

                foreach ($ruleDetails as $key => $ruleDetail) {
                    if (!isset($this->_rewardPointDetail[$key])) {
                        $this->_rewardPointDetail[$key] = 0;
                    }
                    $this->_rewardPointDetail[$key] += $ruleDetail;
                }

                $this->_earnRewardPoint     += $rewardPoint + $mwRewardPoint;
                $this->_earnRewardPointCart += $rewardPoint;
            }
        }

        $this->_checkoutSession->setQuoteIdSession('');
        $spendSubMoneyToPoint = 0;

        if ($spendSubMoney >= 0) {
            $spendSubMoney        += $tax + $shipping;
            $spendSubMoneyToPoint = $this->_dataHelper->exchangeMoneysToPoints($spendSubMoney, $store->getCode());
        }
        $spendRewardPointCart = $this->_dataHelper->roundPoints(
            $spendSubPoint + $spendSubMoneyToPoint,
            $store->getCode(),
            false
        );
        $pointCheckout = $this->_dataHelper->exchangeMoneysToPoints(
            $total->getBaseGrandTotal() - $newTax - $newShipping,
            $store->getCode()
        );

        if ($pointCheckout < 0) {
            $pointCheckout = 0;
        }
        if ($spendRewardPointCart > $pointCheckout) {
            $spendRewardPointCart = $pointCheckout;
        }
        if ($quote->getMwRewardpoint() == $pointCheckout) {
            $quote->setMwRewardpointDiscount($total->getBaseGrandTotal() - $newTax - $newShipping);
        }

        $mwRewardpointDiscount = $quote->getMwRewardpointDiscount();
        if ($mwRewardpointDiscount == 0) {
            $totalDiscountAmount = 0;
        } else {
            $totalDiscountAmount = $this->_priceCurrency->convert($mwRewardpointDiscount);
        }
        $baseTotalDiscountAmount = $mwRewardpointDiscount;
        $quote->setMwRewardpointDiscount($mwRewardpointDiscount);
        $quote->setMwRewardpointDiscountShow($totalDiscountAmount);

        if ($customerId) {
            if (!$this->_dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $this->_earnRewardPoint)) {
                $this->_earnRewardPoint     = 0;
                $this->_earnRewardPointCart = 0;
                $this->_rewardPointDetail = [];
            }
        }

        // Add option allow using reward points and coupon code at the same time
        if (!$this->_dataHelper->getCouponRwpConfig($store->getCode())) {
            if ($quote->getCouponCode() != '') {
                $quote->setSpendRewardpointCart(0);
            } else if ($quote->getMwRewardpoint() > 0) {
                $quote->setCouponCode('');
            } else {
                $quote->setSpendRewardpointCart($spendRewardPointCart);
            }
        } else {
            $quote->setSpendRewardpointCart($spendRewardPointCart);
        }

        $quote->setEarnRewardpoint($this->_earnRewardPoint)
            ->setEarnRewardpointCart($this->_earnRewardPointCart)
            ->setMwRewardpointSellProduct($productSellPoint)
            ->setMwRewardpointDetail(serialize($this->_rewardPointDetail))
            ->setMwRewardpointRuleMessage(serialize($this->_arrayRuleActive))
            ->save();

        if ($quote->getMwRewardpoint() <= 0 || $totalDiscountAmount <= 0 || $baseTotalDiscountAmount <= 0) {
            $address->setMwRewardpoint(0)
                ->setMwRewardpointDiscountShow(0)
                ->setMwRewardpointDiscount(0);
        } else {
            $address->setMwRewardpoint($quote->getMwRewardpoint())
                ->setMwRewardpointDiscountShow($totalDiscountAmount)
                ->setMwRewardpointDiscount($baseTotalDiscountAmount);
        }

        $this->_sessionManager->setMwRewardpointDiscountShowTotal($address->getMwRewardpointDiscountShow())
            ->setMwRewardpointDiscountTotal($address->getMwRewardpointDiscount());

        $address->setGrandTotal($total->getGrandTotal() - $address->getMwRewardpointDiscountShow())
            ->setBaseGrandTotal($total->getBaseGrandTotal() - ($address->getMwRewardpointDiscount()));

        // Set total
        $totals = array_sum($total->getAllTotalAmounts());
        $baseTotals = array_sum($total->getAllBaseTotalAmounts());
        $total->setGrandTotal($totals - $address->getMwRewardpointDiscountShow());
        $total->setBaseGrandTotal($baseTotals - $address->getMwRewardpointDiscount());

        return $this;
    }

    /**
     * Collect reward points when using the configuration: Calculate to TAX - After Redeempoint
     *
     * @param $address
     * @param $store
     * @param $quote
     * @param $items
     * @param $tax
     * @param $shipping
     * @param $total
     * @return $this
     */
    public function collectWhenCalculateToTaxAfterRedeemPoints($address, $store, $quote, $items, $tax, $shipping, $total)
    {
        if (!count($items)) {
            return $this;
        }

        $baseMwRewardpointDiscount = $quote->getMwRewardpointDiscount();
        if ($baseMwRewardpointDiscount == 0) {
            $mwRewardpointDiscount = 0;
        } else {
            $mwRewardpointDiscount = $this->_priceCurrency->convert($baseMwRewardpointDiscount);
        }

        // Total quantity of items
        $items = $address->getAllVisibleItems();
        $itemsQty = 0;
        foreach ($items as $item) {
            $itemsQty += $item->getQty();
        }
        // Calculate redeem tax amount
        $baseDiscountForEachItem = round(($baseMwRewardpointDiscount / $itemsQty), 2);
        $baseRedeemTaxAmount = 0;
        foreach ($items as $item) {
            $baseRedeemTaxAmount += round(($baseDiscountForEachItem * $item->getQty() * $item->getTaxPercent() / 100), 2);
        }

        if ($baseRedeemTaxAmount == 0) {
            $redeemTaxAmount = 0;
        } else {
            $redeemTaxAmount = $this->_priceCurrency->convert($baseRedeemTaxAmount);
        }

        // Set new tax amount after redeem points
        $total->setTaxAmount($total->getTaxAmount() - $redeemTaxAmount)
            ->setBaseTaxAmount($total->getBaseTaxAmount() - $baseRedeemTaxAmount)
            ->setGrandTotal($total->getGrandTotal() - $redeemTaxAmount)
            ->setBaseGrandTotal($total->getBaseGrandTotal() - $baseRedeemTaxAmount);

        // Calculate Reward Points Earned configuration
        $applyReward          = (int) $this->_dataHelper->getApplyRewardPoints($store->getCode());
        $applyRewardPointsTax = (int) $this->_dataHelper->getApplyRewardPointsTax($store->getCode());
        if ($applyReward == Applyreward::BEFORE) {
            // Before discount
            if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                // Before tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotal();
            } else {
                // After tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotalInclTax() - $baseRedeemTaxAmount;
            }
        } else {
            // After discount
            if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                // Before tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotal() + $total->getBaseDiscountAmount();
            } else {
                // After tax
                $baseSubtotalWithDiscount = $total->getBaseSubtotal() + $total->getBaseDiscountAmount() + $total->getBaseTaxAmount();
            }
        }

        // Get programs for spending point
        $spendSubtractPoint = 0;
        $spendSubtractMoney = 0;
        $spendPrograms      = $this->getSpendProgramResult();

        // Get programs for earning point
        $programs            = $this->getEarnProgramResult();
        $productSellPoint    = 0;

        // Calculate earning points and sending points for each item and child item (bundle, configurable)
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $qty       = $item->getQty();
            $product   = $this->_productFactory->create()->load($productId);

            // Catalog earning rule
            if ($product->getTypeId() != 'bundle') {
                $mwRewardPoint = $qty * $this->_catalogrulesFactory->create()->getPointCatalogRule($productId);
            } else {
                $mwRewardPoint = 0;
            }

            // Sell points
            switch ($product->getTypeId()) {
                case 'simple':
                case 'virtual':
                case 'downloadable':
                    $mwRewardPointSell = $product->getMwRewardPointSellProduct();
                    if ($mwRewardPointSell > 0) {
                        $productSellPoint += $qty * $mwRewardPointSell;
                    }
                    break;
                case 'bundle':
                    $mwRewardPointSell = $product->getMwRewardPointSellProduct();
                    if ($mwRewardPointSell > 0) {
                        $productSellPoint += $qty * $mwRewardPointSell;
                    } else {
                        foreach ($item->getChildren() as $bundleItem) {
                            $childProduct   = $this->_productFactory->create()->load($bundleItem->getProductId());
                            $childPointSell = $childProduct->getMwRewardPointSellProduct();

                            if ($childPointSell > 0) {
                                $productSellPoint += $bundleItem->getQty() * $childPointSell;
                            }
                        }
                    }

                    break;
                case 'configurable':
                    // Total points = parent points + all children points
                    $mwRewardPointSell = $product->getMwRewardPointSellProduct();
                    if ($mwRewardPointSell > 0) {
                        $productSellPoint += $qty * $mwRewardPointSell;
                    } else {
                        if ($info = $item->getProduct()->getCustomOption('info_buyRequest')) {
                            $infoArr = unserialize($info->getValue());

                            foreach ($infoArr['super_attribute'] as $attributeId => $value) {
                                $collection = $this->_productsellpointFactory->create()->getCollection()
                                    ->addFieldToFilter('product_id', $product->getId())
                                    ->addFieldToFilter('option_id', $value)
                                    ->addFieldToFilter('option_type_id', $attributeId)
                                    ->addFieldToFilter('type_id', 'super_attribute')
                                    ->getFirstItem();

                                $productSellPoint += intval($collection->getSellPoint());
                            }
                        }
                    }

                    $mwRewardPointEarn = $product->getRewardPointProduct();
                    if ($mwRewardPointEarn > 0) {
                        $this->_earnRewardPoint += $qty * $mwRewardPointEarn;
                    } else {
                        if ($info = $item->getProduct()->getCustomOption('info_buyRequest')) {
                            $infoArr = unserialize($info->getValue());

                            foreach ($infoArr['super_attribute'] as $attributeId => $value) {
                                $collection = $this->_productsellpointFactory->create()->getCollection()
                                    ->addFieldToFilter('product_id', $product->getId())
                                    ->addFieldToFilter('option_id', $value)
                                    ->addFieldToFilter('option_type_id', $attributeId)
                                    ->addFieldToFilter('type_id', 'super_attribute')
                                    ->getFirstItem();

                                $this->_earnRewardPoint += intval($collection->getEarnPoint());
                            }
                        }
                    }
                    break;
            }

            $priceWithDiscount = $item->getBasePrice() - $item->getBaseDiscountAmount() / $qty;
            if ($applyReward == Applyreward::BEFORE) {
                if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                    $price = $item->getBasePrice();
                } else {
                    $price = $item->getBasePriceInclTax();
                }
            } else {
                if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                    $price = $priceWithDiscount;
                } else {
                    $itemDiscountTaxAmount = ($item->getBaseDiscountAmount() * $item->getTaxPercent() / 100) / $qty;
                    $price = $item->getBasePriceInclTax() - $item->getBaseDiscountAmount() / $qty - $itemDiscountTaxAmount;
                }
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $mwRewardPointBundle = 0;

                foreach ($item->getChildren() as $child) {
                    $qtyChild = $child->getQty();
                    $childPriceWithDiscount = $child->getBasePrice() - $child->getBaseDiscountAmount() / $qtyChild;

                    if ($applyReward == Applyreward::BEFORE) {
                        if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                            $childPrice = $child->getBasePrice();
                        } else {
                            $childPrice = $child->getBasePriceInclTax();
                        }
                    } else {
                        if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                            $childPrice = $childPriceWithDiscount;
                        } else {
                            $childDiscountTaxAmount = ($child->getBaseDiscountAmount() * $child->getTaxPercent() / 100) / $qtyChild;
                            $childPrice = $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() / $qtyChild - $childDiscountTaxAmount;
                        }
                    }

                    $arraySpendRewardPoint = $this->spendProcessRule(
                        $child,
                        $spendPrograms,
                        $qtyChild,
                        $childPriceWithDiscount
                    );
                    if ((int)$arraySpendRewardPoint[1] > 0) {
                        $spendSubtractPoint += $qty * $arraySpendRewardPoint[1];
                    }
                    if ((double)$arraySpendRewardPoint[2] > 0) {
                        $spendSubtractMoney += $qty * $arraySpendRewardPoint[2];
                    }

                    $rewardPointArray = $this->processRuleConfigurableProduct(
                        $child,
                        $programs,
                        $qtyChild,
                        $childPrice,
                        $qty,
                        $baseSubtotalWithDiscount
                    );
                    $rewardPoint = $rewardPointArray[1];
                    $ruleDetails = $rewardPointArray[2];

                    foreach ($ruleDetails as $key => $ruleDetail) {
                        if (!isset($this->_rewardPointDetail[$key])) {
                            $this->_rewardPointDetail[$key] = 0;
                        }
                        $this->_rewardPointDetail[$key] += $ruleDetail;
                    }

                    if ($product->getTypeId() == 'bundle') {
                        // Check if spend Y get X point
                        $mwRewardPoint = 0;
                        $mwRewardPointBundle += $qtyChild * $this->_catalogrulesFactory->create()->getPointCatalogRule($child->getProductId());
                    }

                    $this->_earnRewardPoint     += $rewardPoint + $mwRewardPoint;
                    $this->_earnRewardPointCart += $rewardPoint;
                }

                if ($product->getTypeId() == 'bundle') {
                    $this->_earnRewardPoint += $mwRewardPointBundle * $qty;
                }
            } else {
                $arraySpendRewardPoint = $this->spendProcessRule(
                    $item,
                    $spendPrograms,
                    $qty,
                    $priceWithDiscount
                );

                if ((int)$arraySpendRewardPoint[1] > 0) {
                    $spendSubtractPoint += $arraySpendRewardPoint[1];
                }
                if ((double)$arraySpendRewardPoint[2] > 0) {
                    $spendSubtractMoney += $arraySpendRewardPoint[2];
                }

                $rewardPointArray = $this->processRule(
                    $item,
                    $programs,
                    $qty,
                    $price,
                    $baseSubtotalWithDiscount
                );
                $rewardPoint = $rewardPointArray[1];
                $ruleDetails = $rewardPointArray[2];

                foreach ($ruleDetails as $key => $ruleDetail) {
                    if (!isset($this->_rewardPointDetail[$key])) {
                        $this->_rewardPointDetail[$key] = 0;
                    }
                    $this->_rewardPointDetail[$key] += $ruleDetail;
                }
                $this->_earnRewardPoint     += $rewardPoint;
                $this->_earnRewardPointCart += $rewardPoint;
            }
        }

        $this->_checkoutSession->setQuoteIdSession('');
        $spendSubtractMoneyToPoint = 0;

        if ($spendSubtractMoney >= 0) {
            $spendSubtractMoney        += $tax + $shipping;
            $spendSubtractMoneyToPoint = $this->_dataHelper->exchangeMoneysToPoints(
                $spendSubtractMoney,
                $store->getCode()
            );
        }
        $spendRewardPointCart = $this->_dataHelper->roundPoints(
            $spendSubtractPoint + $spendSubtractMoneyToPoint,
            $store->getCode(),
            false
        );

        $customerId = $quote->getCustomerId();
        if ($customerId) {
            if (!$this->_dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $this->_earnRewardPoint)) {
                $this->_earnRewardPoint     = 0;
                $this->_earnRewardPointCart = 0;
                $this->_rewardPointDetail = [];
            }
        }

        // Add option allow using reward points and coupon code at the same time
        if (!$this->_dataHelper->getCouponRwpConfig($store->getCode())) {
            if ($quote->getCouponCode() != '') {
                $quote->setSpendRewardpointCart(0);
                $this->_sessionManager->setMwRewardpointAfterDrop(0);
            } else if ($quote->getMwRewardpoint() > 0) {
                $quote->setCouponCode('')
                    ->setSpendRewardpointCart($spendRewardPointCart);
            } else {
                $quote->setSpendRewardpointCart($spendRewardPointCart);
            }
        } else {
            $quote->setSpendRewardpointCart($spendRewardPointCart);
        }

        if ($quote->getMwRewardpoint() > $spendRewardPointCart) {
            $quote->setMwRewardpoint($spendRewardPointCart);
            $this->_sessionManager->setMwRewardpointAfterDrop($spendRewardPointCart);
        }

        $quote->setEarnRewardpoint($this->_earnRewardPoint)
            ->setEarnRewardpointCart($this->_earnRewardPointCart)
            ->setMwRewardpointSellProduct($productSellPoint)
            ->setMwRewardpointDetail(serialize($this->_rewardPointDetail))
            ->setMwRewardpointRuleMessage(serialize($this->_arrayRuleActive))
            ->save();

        $taxAmount = $total->getTaxAmount();
        $baseTaxAmount = $total->getBaseTaxAmount();
        $subtotalExlTax    = $total->getSubtotal();
        $grandTotalExlTax  = $total->getGrandTotal() - $taxAmount;
        $allShippingAmount = $grandTotalExlTax - $subtotalExlTax;

        $totalDiscountAmount = $subtotalExlTax - $mwRewardpointDiscount + $allShippingAmount + $taxAmount;
        $baseTotalDiscountAmount = $subtotalExlTax - $baseMwRewardpointDiscount + $allShippingAmount + $baseTaxAmount;

        if ($quote->getMwRewardpoint() <= 0 || $totalDiscountAmount <= 0 || $baseTotalDiscountAmount <= 0) {
            $address->setMwRewardpoint(0)
                ->setMwRewardpointDiscountShow(0)
                ->setMwRewardpointDiscount(0);
        } else {
            $address->setMwRewardpoint($quote->getMwRewardpoint())
                ->setMwRewardpointDiscountShow($mwRewardpointDiscount)
                ->setMwRewardpointDiscount($baseMwRewardpointDiscount);
        }

        $this->_sessionManager->setMwRewardpointDiscountShowTotal($address->getMwRewardpointDiscountShow())
            ->setMwRewardpointDiscountTotal($address->getMwRewardpointDiscount());

        $address->setGrandTotal($total->getGrandTotal() - $mwRewardpointDiscount)
            ->setBaseGrandTotal($total->getBaseGrandTotal() - $baseMwRewardpointDiscount);

        // Set total
        $total->addTotalAmount('tax', -$redeemTaxAmount);
        $total->addBaseTotalAmount('tax', -$baseRedeemTaxAmount);
        $totals = array_sum($total->getAllTotalAmounts());
        $baseTotals = array_sum($total->getAllBaseTotalAmounts());
        $total->setGrandTotal($totals - $mwRewardpointDiscount);
        $total->setBaseGrandTotal($baseTotals - $baseMwRewardpointDiscount);

        return $this;
    }

    /**
     * @return array
     */
    public function getSpendProgramResult()
    {
        $spendPrograms = $this->_spendcartrulesFactory->create();
        $programIds = $this->getAllProgram($spendPrograms);

        return $programIds;
    }

    /**
     * @return array
     */
    public function getEarnProgramResult()
    {
        $earnPrograms = $this->_cartrulesFactory->create();
        $programIds = $this->getAllProgram($earnPrograms);

        return $programIds;
    }

    /**
     * @param $model
     * @return array
     */
    public function getAllProgram($model)
    {
        $programIds = [];
        // Check programs by enable
        $collection = $model->getCollection()
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
                            $position[] = (int) $program->getRulePosition();
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

        foreach ($programs as $program) {
            $programRule[]  = $program;
            $rule           = $this->_cartrulesFactory->create()->load($program);
            $rewardPoint    = (int)$rule->getRewardPoint();
            $simpleAction   = (int)$rule->getSimpleAction();
            $rewardStep     = (int)$rule->getRewardStep();
            $stopRule       = (int)$rule->getStopRulesProcessing();
            $rule->afterLoad();
            $address = $this->getAddress($item);

            if (($rule->getConditions()->validate($address)) && ($rule->getActions()->validate($item))) {
                switch ($simpleAction) {
                    case Typerule::FIXED:
                        $resultRewardPoint += $rewardPoint;
                        if (!isset($resultDetail[$program])) {
                            $resultDetail[$program] = 0;
                        }

                        $resultDetail[$program] += $rewardPoint;
                        break;
                    case Typerule::FIXED_WHOLE_CART:
                        if (!(isset($this->_arrayWholeCart[$program]) && $this->_arrayWholeCart[$program] == 1)) {
                            $this->_arrayWholeCart[$program] = 1;
                            $resultRewardPoint += $rewardPoint;
                            if (!isset($resultDetail[$program])) {
                                $resultDetail[$program] = 0;
                            }

                            $resultDetail[$program] += $rewardPoint;
                        }
                        break;
                    case Typerule::BUY_X_GET_Y_WHOLE_CART:
                        if ($rewardStep > 0) {
                            if(!(isset($this->_arrayWholeCartXY[$program]) && $this->_arrayWholeCartXY[$program] == 1)) {
                                $this->_arrayWholeCartXY[$program] = 1;
                                $resultRewardPoint += (int)($baseSubtotalWithDiscount / $rewardStep) * $rewardPoint;
                                if (!isset($resultDetail[$program])) {
                                    $resultDetail[$program] = 0;
                                }

                                $resultDetail[$program] += (int)($baseSubtotalWithDiscount / $rewardStep) * $rewardPoint;
                            }
                        }
                        break;
                    default:
                        if ($rewardStep > 0) {
                            $resultRewardPoint += (int)(($qty * $price) / $rewardStep) * $rewardPoint;
                            if (!isset($resultDetail[$program])) {
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
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param $programs
     * @param $qty
     * @param $price
     * @param $qtyParent
     * @param $baseSubtotalWithDiscount
     * @return array
     */
    public function processRuleConfigurableProduct(
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $programs,
        $qty,
        $price,
        $qtyParent,
        $baseSubtotalWithDiscount
    ) {
        $result            = [];
        $resultRewardPoint = 0;
        $resultDetail      = [];
        $programRule       = [];

        foreach ($programs as $program) {
            $programRule[]  = $program;
            $rule           = $this->_cartrulesFactory->create()->load($program);
            $rewardPoint    = (int)$rule->getRewardPoint();
            $simpleAction   = (int)$rule->getSimpleAction();
            $rewardStep     = (int)$rule->getRewardStep();
            $stopRule       = (int)$rule->getStopRulesProcessing();
            $rule->afterLoad();
            $address = $this->getAddress($item);

            if (($rule->getConditions()->validate($address)) && ($rule->getActions()->validate($item))) {
                switch ($simpleAction) {
                    case Typerule::FIXED:
                        $resultRewardPoint = $resultRewardPoint + $rewardPoint;
                        if (!isset($resultDetail[$program])) {
                            $resultDetail[$program] = 0;
                        }

                        $resultDetail[$program] += $rewardPoint;
                        break;
                    case Typerule::FIXED_WHOLE_CART:
                        if (!(isset($this->_arrayWholeCart[$program]) && $this->_arrayWholeCart[$program] == 1)) {
                            $this->_arrayWholeCart[$program] = 1;
                            $resultRewardPoint += $rewardPoint;
                            if (!isset($resultDetail[$program])) {
                                $resultDetail[$program] = 0;
                            }

                            $resultDetail[$program] += $rewardPoint;
                        }
                        break;
                    case Typerule::BUY_X_GET_Y_WHOLE_CART:
                        if ($rewardStep > 0) {
                            if(!(isset($this->_arrayWholeCartXY[$program]) && $this->_arrayWholeCartXY[$program] == 1)) {
                                $this->_arrayWholeCartXY[$program] = 1;
                                $resultRewardPoint += (int)($baseSubtotalWithDiscount / $rewardStep) * $rewardPoint;
                                if (!isset($resultDetail[$program])) {
                                    $resultDetail[$program] = 0;
                                }

                                $resultDetail[$program] += (int)($baseSubtotalWithDiscount / $rewardStep) * $rewardPoint;
                            }
                        }
                        break;
                    default:
                        if ($rewardStep > 0) {
                            $resultRewardPoint += (int)(($qty * $price * $qtyParent) / $rewardStep) * $rewardPoint;
                            if (!isset($resultDetail[$program])) {
                                $resultDetail[$program] = 0;
                            }

                            $resultDetail[$program] += (int)(($qty * $price * $qtyParent) / $rewardStep) * $rewardPoint;
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
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param $programs
     * @param $qty
     * @param $price
     * @return array
     */
    public function spendProcessRule(
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $programs,
        $qty,
        $price
    ) {
        $result    = [];
        $result[1] = 0;
        $result[2] = $qty * $price;

        foreach ($programs as $program) {
            $rule         = $this->_spendcartrulesFactory->create()->load($program);
            $rewardPoint  = (int)$rule->getRewardPoint();
            $stopRule     = (int)$rule->getStopRulesProcessing();
            $simpleAction = (int)$rule->getSimpleAction();
            $rewardStep   = (int)$rule->getRewardStep();
            $rule->afterLoad();
            $address = $this->getAddress($item);

            if (($rule->getConditions()->validate($address)) && ($rule->getActions()->validate($item))) {
                switch ($simpleAction) {
                    case Typerulespend::FIXED:
                        if ($this->_checkoutSession->getQuoteIdSession() == $item->getQuoteId()) {
                            $result[1] = 0;
                            $result[2] = 0;
                        } else {
                            $result[1] += $rewardPoint;
                            $result[2] = 0;
                        }

                        $this->_checkoutSession->setQuoteIdSession($item->getQuoteId());
                        break;
                    case Typerulespend::BUY_X_USE_Y:
                        if ($rewardStep > 0) {
                            $result[1] += (int)(($qty * $price) / $rewardStep) * $rewardPoint;
                        }

                        $result[2] = 0;
                        break;
                    case Typerulespend::USE_UNLIMIT_POINTS:
                        $result[1] = 0;
                        $result[2] = $qty * $price;
                        break;
                    case Typerulespend::NOT_ALLOW_USE_POINTS:
                        $result[1] = 0;
                        $result[2] = 0;
                        break;
                }
            }

            if ($stopRule) {
                break;
            }
        }

        return $result;
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
