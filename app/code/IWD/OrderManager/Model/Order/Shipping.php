<?php

namespace IWD\OrderManager\Model\Order;

use IWD\OrderManager\Model\Log\Logger;
use IWD\OrderManager\Model\Quote\Quote;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Shipping
 * @package IWD\OrderManager\Model\Order
 */
class Shipping extends AbstractModel
{
    const CUSTOM_METHOD_CODE = 'iwd_om_custom_shipping';

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $shippingMethod;

    /**
     * @var string
     */
    private $shippingDescription;

    /**
     * @var float
     */
    private $shippingPrice;

    /**
     * @var float
     */
    private $shippingPriceInclTax;

    /**
     * @var float
     */
    private $taxPercent;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var \Magento\Shipping\Model\Shipping
     */
    private $shipping;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    private $taxCalculation;

    /**
     * @var \Magento\Customer\Model\Group
     */
    private $customerGroup;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    private $shippingRateRequest;

    /**
     * @var null
     */
    private $rate;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory
     */
    private $rateCollectionFactory;

    /**
     * @var \IWD\OrderManager\Helper\Data
     */
    private $omHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\OrderManager\Helper\Data $omHelper
     * @param Order $order
     * @param Quote $quote
     * @param \Magento\Shipping\Model\Shipping $shipping
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Customer\Model\Group $customerGroup
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $shippingRateRequest
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\OrderManager\Helper\Data $omHelper,
        Order $order,
        Quote $quote,
        \Magento\Shipping\Model\Shipping $shipping,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\Group $customerGroup,
        \Magento\Quote\Model\Quote\Address\RateRequest $shippingRateRequest,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->order = $order;
        $this->quote = $quote;
        $this->shipping = $shipping;
        $this->directoryHelper = $directoryHelper;
        $this->omHelper = $omHelper;
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
        $this->customerGroup = $customerGroup;
        $this->shippingRateRequest = $shippingRateRequest;
        $this->rate = null;

        $this->quoteRepository = $quoteRepository;

        $this->rateCollectionFactory = $rateCollectionFactory;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingDescription()
    {
        return $this->shippingDescription;
    }

    /**
     * @param string $shippingDescription
     * @return $this
     */
    public function setShippingDescription($shippingDescription)
    {
        $this->shippingDescription = $shippingDescription;
        return $this;
    }

    /**
     * @param float $shippingPrice
     * @return $this
     */
    public function setShippingPrice($shippingPrice)
    {
        $this->shippingPrice = $shippingPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     * @param float $shippingPriceInclTax
     * @return $this
     */
    public function setShippingPriceInclTax($shippingPriceInclTax)
    {
        $this->shippingPriceInclTax = $shippingPriceInclTax;
        return $this;
    }

    /**
     * @return float
     */
    public function getShippingPriceInclTax()
    {
        return $this->shippingPriceInclTax;
    }

    /**
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent)
    {
        $this->taxPercent = $taxPercent;
        return $this;
    }

    /**
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->taxPercent;
    }

    /**
     * @param [] $params
     * @return void
     */
    public function initParams($params)
    {
        if (isset($params['order_id'])) {
            $this->setOrderId($params['order_id']);
        }
        if (isset($params['shipping_method'])) {
            $this->setShippingMethod($params['shipping_method']);
        }
        if (isset($params['price_excl_tax'])) {
            $this->setShippingPrice($params['price_excl_tax']);
        }
        if (isset($params['price_incl_tax'])) {
            $this->setShippingPriceInclTax($params['price_incl_tax']);
        }
        if (isset($params['tax_percent'])) {
            $this->setTaxPercent($params['tax_percent']);
        }
        if (isset($params['description'])) {
            $this->setShippingDescription($params['description']);
        }
    }

    /**
     * @return void
     */
    public function updateShippingMethod()
    {
        $this->loadOrder();

        $baseShippingInclTax = $this->getShippingPriceInclTax();
        $baseShippingAmount = $this->getShippingPrice();
        $baseShippingTaxAmount = $baseShippingInclTax - $baseShippingAmount;

        /* convert currency */
        $baseCurrencyCode = $this->order->getBaseCurrencyCode();
        $orderCurrencyCode = $this->order->getOrderCurrencyCode();
        if ($baseCurrencyCode === $orderCurrencyCode) {
            $shippingAmount = $baseShippingAmount;
            $shippingInclTax = $baseShippingInclTax;
            $shippingTaxAmount = $baseShippingTaxAmount;
        } else {
            $shippingAmount = $this->directoryHelper->currencyConvert(
                $baseShippingAmount,
                $baseCurrencyCode,
                $orderCurrencyCode
            );
            $shippingInclTax = $this->directoryHelper->currencyConvert(
                $baseShippingInclTax,
                $baseCurrencyCode,
                $orderCurrencyCode
            );
            $shippingTaxAmount = $this->directoryHelper->currencyConvert(
                $baseShippingTaxAmount,
                $baseCurrencyCode,
                $orderCurrencyCode
            );
        }

        Logger::getInstance()->addChange(
            'Shipping method',
            $this->order->getShippingDescription(),
            $this->getShippingDescription(),
            'shipping_info'
        );
        Logger::getInstance()->addChange(
            'Shipping amount',
            $this->order->getShippingAmount(),
            $shippingAmount,
            'shipping_info',
            Logger::FORMAT_PRICE
        );
        Logger::getInstance()->addChange(
            'Shipping amount incl. tax',
            $this->order->getShippingInclTax(),
            $shippingInclTax,
            'shipping_info',
            Logger::FORMAT_PRICE
        );

        $this->order
            ->setShippingDescription($this->getShippingDescription())
            ->setData('shipping_method', $this->getShippingMethod())
            ->setShippingAmount($shippingAmount)
            ->setBaseShippingAmount($baseShippingAmount)
            ->setShippingInclTax($shippingInclTax)
            ->setBaseShippingInclTax($baseShippingInclTax)
            ->setShippingTaxAmount($shippingTaxAmount)
            ->setBaseShippingTaxAmount($baseShippingTaxAmount)
            ->save();

        $this->order->calculateGrandTotal();
        $this->order->save();
    }

    /**
     * @return void
     */
    private function loadOrder()
    {
        $id = $this->getOrderId();
        $this->order->load($id);
        $this->order->initAfterLoad();
    }

    /**
     * @return void
     */
    public function recollectShippingAmount()
    {
        $this->loadOrder();

        if ($this->order->getShippingMethod() == self::CUSTOM_METHOD_CODE) {
            $this->recollectCustomShippingMethod();
        }

        $this->recollectStandardShippingMethod();
    }

    /**
     * @return void
     */
    private function recollectCustomShippingMethod()
    {
        $taxAmount = $this->order->getTaxAmount() + $this->order->getShippingTaxAmount();
        $baseTaxAmount = $this->order->getBaseTaxAmount() + $this->order->getBaseShippingTaxAmount();

        $this->order
            ->setTaxAmount($taxAmount)
            ->setBaseTaxAmount($baseTaxAmount)
            ->save();
    }

    /**
     * @return void
     */
    private function recollectStandardShippingMethod()
    {
        $rate = $this->getCurrentShippingRate();
        $basePrice = $rate->getPrice();
        $price = $this->directoryHelper->currencyConvert(
            $rate->getPrice(),
            $this->order->getBaseCurrencyCode(),
            $this->order->getOrderCurrencyCode()
        );

        $this->collectShipping($price, $basePrice);
    }

    /**
     * @param float $shippingAmount
     * @param float $baseShippingAmount
     * @return float
     */
    private function collectShipping($shippingAmount, $baseShippingAmount)
    {
        $store = $this->order->getStore();

        $shippingTaxClass = $this->taxConfig->getShippingTaxClass($store);
        $shippingPriceIncludesTax = $this->taxConfig->shippingPriceIncludesTax($store);
        $shippingTaxAmount = 0;
        $baseShippingTaxAmount = 0;

        if ($shippingTaxClass) {
            $rateRequest = $this->getRateRequest()->setProductClassId($shippingTaxClass);
            $rate = $this->taxCalculation->getRate($rateRequest);

            if ($rate) {
                $shippingTaxAmount = $shippingAmount - $shippingAmount / (1 + $rate / 100);
                $shippingTaxAmount = $this->omHelper->roundPrice($shippingTaxAmount);
                $this->order->setTaxAmount($this->order->getTaxAmount() + $shippingTaxAmount);

                $baseShippingTaxAmount = $baseShippingAmount - $baseShippingAmount / (1 + $rate / 100);
                $baseShippingTaxAmount = $this->omHelper->roundPrice($baseShippingTaxAmount);
                $this->order->setBaseTaxAmount($this->order->getBaseTaxAmount() + $baseShippingTaxAmount);
            }
        }

        if ($shippingPriceIncludesTax) {
            $baseSippingInclTax = $baseShippingAmount;
            $baseSippingAmount = $baseShippingAmount - $baseShippingTaxAmount;
            $sippingInclTax = $shippingAmount;
            $sippingAmount = $shippingAmount - $shippingTaxAmount;
        } else {
            $baseSippingInclTax = $baseShippingAmount + $baseShippingTaxAmount;
            $baseSippingAmount = $baseShippingAmount;
            $sippingInclTax = $shippingAmount + $shippingTaxAmount;
            $sippingAmount = $shippingAmount;
        }

        $this->order
            ->setShippingInclTax($sippingInclTax)
            ->setBaseShippingInclTax($baseSippingInclTax)
            ->setShippingTaxAmount($shippingTaxAmount)
            ->setBaseShippingTaxAmount($baseShippingTaxAmount)
            ->setShippingAmount($sippingAmount)
            ->setBaseShippingAmount($baseSippingAmount)
            ->save();

        return $baseSippingInclTax;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function getRateRequest()
    {
        $store = $this->order->getStore();
        $customerTaxClassId = $this->order->getCustomer()->getTaxClassId();
        $shippingAddress = $this->order->getShippingAddress();
        $billingAddress = $this->order->getBillingAddress();

        return $this->taxCalculation->getRateRequest($shippingAddress, $billingAddress, $customerTaxClassId, $store);
    }

    /**
     * @return void
     */
    public function reloadShippingRates()
    {
        $this->getOrder()->syncQuote();
    }

    /**
     * @return null
     */
    private function getCurrentShippingRate()
    {
        if (empty($this->rate)) {
            $orderShippingCode = $this->getOrder()->getShippingMethod();

            $this->reloadShippingRates();

            $this->rate = $this->quote->load($this->getOrder()
                ->getQuoteId())
                ->getShippingAddress()
                ->getShippingRateByCode($orderShippingCode);

            $this->rate = $this->getShippingRateByCode($orderShippingCode);
        }

        return $this->rate;
    }

    /**
     * NOTE: We can not use
     * app/code/Magento/Quote/Model/Quote/Address::getShippingRateByCode($code)
     * because it using old rates
     *
     * @param string $orderShippingCode
     * @return bool
     */
    private function getShippingRateByCode($orderShippingCode)
    {
        $address = $this->getQuote()->getShippingAddress();
        $id = $address->getId();
        $rates = $this->rateCollectionFactory->create()->setAddressFilter($id);
        foreach ($rates as $rate) {
            if ($rate->getCode() == $orderShippingCode) {
                $rate->setAddress($address);
                return $rate;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isNotAvailable()
    {
        return $this->getCurrentShippingRate() == null;
    }

    /**
     * @return bool
     */
    public function isTotalChanged()
    {
        if (!$this->isNotAvailable()) {
            $currentShippingRate = $this->getCurrentShippingRate()->getPrice();
            $shippingAmount = $this->getOrder()->getShippingAmount();

            return $currentShippingRate != $shippingAmount;
        }

        return false;
    }
}
