<?php

namespace IWD\OrderManager\Model\Order;

use IWD\OrderManager\Model\Log\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Model\CouponFactory;

class Order extends \Magento\Sales\Model\Order
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * @var []
     */
    protected $newParams = [];

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Tax\Model\Config $taxConfig
     */
    protected $taxConfig = null;

    /**
     * @var \IWD\OrderManager\Model\Payment\Payment $payment
     */
    protected $payment;

    /**
     * @var \IWD\OrderManager\Model\Order\Sales
     */
    protected $sales;

    /**
     * @var float
     */
    protected $oldTotal;
    
    /**
     * @var float
     */
    protected $oldQtyOrdered;

    /**
     * @var []
     */
    protected $addedItems = [];

    /**
     * @var []
     */
    protected $removedItems = [];
    
    /**
     * @var []
     */
    protected $increasedItems = [];
    
    /**
     * @var []
     */
    protected $decreasedItems = [];

    /**
     * @var []
     */
    protected $changesInAmounts = [];

    /**
     * @var \IWD\OrderManager\Model\Quote\Quote
     */
    protected $quote;

    /**
     * @var \IWD\OrderManager\Model\Invoice\Invoice
     */
    protected $invoice;

    /**
     * @var \IWD\OrderManager\Model\Creditmemo\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var \IWD\OrderManager\Model\Shipment\Shipment
     */
    protected $shipment;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Grid\CollectionFactory
     */
    protected $orderGridCollectionFactory;

    /**
     * @var \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory
     */
    protected $taxCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory
     */
    protected $orderHistoryCollectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param Item $item
     * @param \IWD\OrderManager\Model\Payment\Payment $payment
     * @param \IWD\OrderManager\Model\Quote\Quote $quote
     * @param Sales $sales
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \IWD\OrderManager\Model\Invoice\Invoice $invoice
     * @param \IWD\OrderManager\Model\Shipment\Shipment $shipment
     * @param \IWD\OrderManager\Model\Creditmemo\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $orderHistoryCollectionFactory
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $taxCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Grid\CollectionFactory $orderGridCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CouponFactory $couponFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \IWD\OrderManager\Model\Order\Item $item,
        \IWD\OrderManager\Model\Payment\Payment $payment,
        \IWD\OrderManager\Model\Quote\Quote $quote,
        \IWD\OrderManager\Model\Order\Sales $sales,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \IWD\OrderManager\Model\Invoice\Invoice $invoice,
        \IWD\OrderManager\Model\Shipment\Shipment $shipment,
        \IWD\OrderManager\Model\Creditmemo\Creditmemo $creditmemo,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $orderHistoryCollectionFactory,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $taxCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Grid\CollectionFactory $orderGridCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->taxConfig = $taxConfig;
        $this->item = $item;
        $this->payment = $payment;
        $this->quote = $quote;
        $this->directoryHelper = $directoryHelper;
        $this->sales = $sales;
        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->shipment = $shipment;
        $this->_scopeConfig = $scopeConfig;
        $this->orderGridCollectionFactory = $orderGridCollectionFactory;
        $this->taxCollectionFactory = $taxCollectionFactory;
        $this->orderHistoryCollectionFactory = $orderHistoryCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->couponFactory = $couponFactory;
        $this->customerDataFactory = $customerDataFactory;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $timezone,
            $storeManager,
            $orderConfig,
            $productRepository,
            $orderItemCollectionFactory,
            $productVisibility,
            $invoiceManagement,
            $currencyFactory,
            $eavConfig,
            $orderHistoryFactory,
            $addressCollectionFactory,
            $paymentCollectionFactory,
            $historyCollectionFactory,
            $invoiceCollectionFactory,
            $shipmentCollectionFactory,
            $memoCollectionFactory,
            $trackCollectionFactory,
            $salesOrderCollectionFactory,
            $priceCurrency,
            $productListFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return bool
     */
    public function hasItemsWithIncreasedQty()
    {
        return array_sum($this->increasedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasItemsWithDecreasedQty()
    {
        return array_sum($this->decreasedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasAddedItems()
    {
        return count($this->addedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasRemovedItems()
    {
        return count($this->removedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasChangesInAmounts()
    {
        return count($this->changesInAmounts) > 0;
    }

    /**
     * @return bool
     */
    public function isTotalWasChanged()
    {
        return $this->getChangesInTotal() != 0;
    }

    /**
     * @return float
     */
    public function getChangesInTotal()
    {
        return $this->getOldTotal() - $this->getCurrentOrderTotal();
    }

    /**
     * @return float
     */
    public function getOldTotal()
    {
        return $this->oldTotal;
    }

    /**
     * @return float
     */
    public function getChangesInTotalQty()
    {
        $currentQty = $this->getTotalQtyOrdered();
        return $this->oldQtyOrdered - $currentQty;
    }

    /**
     * @return float
     */
    public function getCurrentOrderTotal()
    {
        return $this->getGrandTotal() - $this->getTotalRefunded();
    }

    /**
     * @return void
     */
    private function beforeEditItems()
    {
        $this->initAfterLoad();
        $this->addedItems = [];
        $this->removedItems = [];
        $this->increasedItems = [];
        $this->decreasedItems = [];
        $this->changesInAmounts = [];
    }

    public function initAfterLoad()
    {
        $this->oldTotal = $this->getCurrentOrderTotal();
        $this->oldQtyOrdered = $this->getTotalQtyOrdered();
    }

    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    public function editItems($params)
    {
        $this->beforeEditItems();

        $this->prepareParamsForEditItems($params);
        $this->checkStatus();
        $this->updateOrderItems();

        $this->collectOrderTotals();
    }

    /**
     * @return void
     */
    public function updatePayment()
    {
        $this->sales->setOrder($this)->updateSalesObjects();

        if ($this->isTotalWasChanged()) {
            $this->payment
                ->setOrder($this)
                ->reauthorizePayment();
        }
    }

    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    protected function prepareParamsForEditItems($params)
    {
        if (!isset($params['order_id']) || !isset($params['item'])) {
            throw new LocalizedException(__('Incorrect params for edit order items'));
        }

        $this->load($params['order_id']);
        $this->newParams = $params['item'];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function checkStatus()
    {
        if (!$this->isAllowEditOrder()) {
            throw new LocalizedException(__("You can't edit order with current status."));
        }
    }

    /**
     * @return void
     */
    protected function updateOrderItems()
    {
        foreach ($this->newParams as $id => $params) {
            $item = $this->loadOrderItem($id, $params);
            $orderItem = $item->editItem($params, $this);
            $this->collectItemsChanges($orderItem);
            $this->editNewItem($orderItem->getItemId(), $params);
        }
    }

    /**
     * @param int $id
     * @param string[] $params
     * @return $this|Item
     */
    protected function loadOrderItem($id, $params)
    {
        $item = clone $this->item;

        if (!isset($params['quote_item'])) {
            if (isset($params['remove']) && $params['remove'] == 1) {
                $this->removedItems[] = $id;
            }
            $item = $item->load($id);
        }

        return $item;
    }

    /**
     * @param Item $orderItem
     * @return void
     */
    protected function collectItemsChanges($orderItem)
    {
        $itemId = $orderItem->getItemId();
        $this->increasedItems[$itemId] = $orderItem->getIncreasedQty();
        $this->decreasedItems[$itemId] = $orderItem->getDecreasedQty();

        $changes = $orderItem->getChangesInAmounts();
        if (!empty($changes)) {
            $this->changesInAmounts[$itemId] = $changes;
        }
    }

    /**
     * @param int $id
     * @param string[] $params
     * @return void
     */
    protected function editNewItem($id, $params)
    {
        if (isset($params['item_type']) && $params['item_type'] == 'quote') {
            $this->addedItems[] = $id;

            unset($params['remove']);
            unset($params['item_type']);

            $item = clone $this->item;
            $item = $item->load($id);
            $item->editItem($params, $this);
        }
    }

    /**
     * @return void
     */
    public function collectOrderTotals()
    {
        $totalQtyOrdered = 0;
        $weight = 0;
        $totalItemCount = 0;
        $baseDiscountTaxCompensationAmount = 0;
        $baseDiscountAmount = 0;
        $baseTotalWeeeDiscount = 0;
        $baseSubtotal = 0;
        $baseSubtotalInclTax = 0;

        /** @var $orderItem \IWD\OrderManager\Model\Order\Item */
        foreach ($this->getItemsCollection() as $orderItem) {
            $baseDiscountAmount += $orderItem->getBaseDiscountAmount();

            //bundle part
            if ($orderItem->getParentItem()) {
                continue;
            }

            $baseDiscountTaxCompensationAmount += $orderItem->getBaseDiscountTaxCompensationAmount();

            $totalQtyOrdered += $orderItem->getQtyOrdered();
            $totalItemCount++;
            $weight += $orderItem->getRowWeight();
            $baseSubtotal += $orderItem->getBaseRowTotal(); /* RowTotal for item is a subtotal */
            $baseSubtotalInclTax += $orderItem->getBaseRowTotalInclTax();
            $baseTotalWeeeDiscount += $orderItem->getBaseDiscountAppliedForWeeeTax();
        }

        /* convert currency */
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $orderCurrencyCode = $this->getOrderCurrencyCode();

        if ($baseCurrencyCode === $orderCurrencyCode) {
            $discountAmount = $baseDiscountAmount;
            $discountTaxCompensationAmount = $baseDiscountTaxCompensationAmount;
            $subtotal = $baseSubtotal;
            $subtotalInclTax = $baseSubtotalInclTax;
        } else {
            $discountAmount = $this->directoryHelper
                ->currencyConvert($baseDiscountAmount, $baseCurrencyCode, $orderCurrencyCode);
            $discountTaxCompensationAmount = $this->directoryHelper
                ->currencyConvert($baseDiscountTaxCompensationAmount, $baseCurrencyCode, $orderCurrencyCode);
            $subtotal = $this->directoryHelper
                ->currencyConvert($baseSubtotal, $baseCurrencyCode, $orderCurrencyCode);
            $subtotalInclTax = $this->directoryHelper
                ->currencyConvert($baseSubtotalInclTax, $baseCurrencyCode, $orderCurrencyCode);
        }

        $this->setTotalQtyOrdered($totalQtyOrdered)
            ->setWeight($weight)
            ->setSubtotal($subtotal)->setBaseSubtotal($baseSubtotal)
            ->setSubtotalInclTax($subtotalInclTax)
            ->setBaseSubtotalInclTax($baseSubtotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensationAmount)
            ->setDiscountAmount('-' . $discountAmount)
            ->setBaseDiscountAmount('-' . $baseDiscountAmount)
            ->setTotalItemCount($totalItemCount);

        $this->save();

        $this->reCalculateTaxAmount();
        $this->calculateGrandTotal();
        $this->updateOrderTaxTable();
    }

    /**
     * @return void
     */
    public function calculateGrandTotal()
    {
        $this->reCalculateTaxAmount();

        // shipping tax
        $tax = $this->getTaxAmount() + $this->getShippingTaxAmount();
        $baseTax = $this->getBaseTaxAmount() + $this->getBaseShippingTaxAmount();

        $this->setTaxAmount($tax)->setBaseTaxAmount($baseTax)->save();

        // Order GrandTotal include tax
        if ($this->checkTaxConfiguration()) {
            $grandTotal = $this->getSubtotal()
                + $this->getTaxAmount()
                + $this->getShippingAmount()
                - abs($this->getDiscountAmount());
            $baseGrandTotal = $this->getBaseSubtotal()
                + $this->getBaseTaxAmount()
                + $this->getBaseShippingAmount()
                - abs($this->getBaseDiscountAmount());
        } else {
            $grandTotal = $this->getSubtotalInclTax()
                + $this->getShippingInclTax()
                - abs($this->getDiscountAmount());
            $baseGrandTotal = $this->getBaseSubtotalInclTax()
                + $this->getBaseShippingInclTax()
                - abs($this->getBaseDiscountAmount());
        }

        $this->setGrandTotal($grandTotal)
            ->setBaseGrandTotal($baseGrandTotal)
            ->save();

        $this->addCustomPriceToOrderGrandTotal();
    }

    /**
     * @return void
     */
    protected function reCalculateTaxAmount()
    {
        $baseTaxAmount = 0;

        /**
         * @var $orderItem \IWD\OrderManager\Model\Order\Item
         */
        foreach ($this->getItemsCollection() as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }
            $baseTaxAmount += $orderItem->getBaseTaxAmount();
        }

        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $orderCurrencyCode = $this->getOrderCurrencyCode();
        if ($baseCurrencyCode === $orderCurrencyCode) {
            $taxAmount = $baseTaxAmount;
        } else {
            $taxAmount = $this->directoryHelper->currencyConvert(
                $baseTaxAmount,
                $baseCurrencyCode,
                $orderCurrencyCode
            );
        }

        $this->setTaxAmount($taxAmount)->setBaseTaxAmount($baseTaxAmount);
        $this->save();
    }

    /**
     * @return bool
     */
    public function checkTaxConfiguration()
    {
        $catalogPrices = $this->taxConfig->priceIncludesTax() ? 1 : 0;
        $shippingPrices = $this->taxConfig->shippingPriceIncludesTax() ? 1 : 0;
        $applyTaxAfterDiscount = $this->taxConfig->applyTaxAfterDiscount() ? 1 : 0;

        return !$catalogPrices && !$shippingPrices && $applyTaxAfterDiscount;
    }

    /**
     * @return void
     */
    public function updateOrderTaxTable()
    {
        //TODO: add logic
    }

    /**
     * @return $this
     */
    public function syncQuote()
    {
        try {
            $this->syncQuoteObj();
            $this->syncQuoteItems();
            $this->syncAddresses();
            $this->collectQuoteTotals();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $this;
    }

    private function syncQuoteObj()
    {
        $quote = $this->getQuote();
        if (empty($quote->getId())) {
            $quote->setStore($this->getStore())
                ->setCustomer($this->getCustomer());

            $this->quoteRepository->save($quote);
            $this->quote = $quote;
            $this->setQuoteId($this->quote->getId())->save();
        }
    }

    /**
     * @return void
     */
    protected function collectQuoteTotals()
    {
        if (!$this->getIsVirtual()) {
            $this->getQuote()->getShippingAddress()
                ->setShippingMethod($this->getShippingMethod())
                ->setCollectShippingRates(true);
        }

        $this->getQuote()->setTotalsCollectedFlag(false);
        $this->getQuote()->collectTotals();

        $this->quoteRepository->save($this->getQuote());
    }

    /**
     * @return void
     */
    protected function syncQuoteItems()
    {
        try {
            $orderItems = [];

            foreach ($this->getItems() as $orderItem) {
                $quoteItemId = $orderItem->getQuoteItemId();
                if ($quoteItemId == null || $this->getQuote()->getItemById($quoteItemId) == null) {
                    $this->restoreQuoteItem($orderItem);
                } else {
                    $orderItems[$quoteItemId] = $orderItem;
                }
            }

            $quoteItemIds = array_keys($orderItems);

            if (count($quoteItemIds) > 0) {
                $quoteItems = $this->getQuote()->getAllItems();
                foreach ($quoteItems as $quoteItem) {
                    try {
                        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
                        $quoteItemId = $quoteItem->getItemId();

                        if (!in_array($quoteItemId, $quoteItemIds)) {
                            $quoteItem->delete();
                        } else {
                            $orderItem = $orderItems[$quoteItemId];
                            $qty = $orderItem->getQtyOrdered() - $orderItem->getQtyRefunded() - $orderItem->getQtyCanceled();

                            $buyRequest = [
                                'qty' => $qty,
                                'custom_price' => $orderItem->getPrice()
                            ];
                            $this->getQuote()->updateItem($quoteItem, $buyRequest)->save();
                        }
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @param $orderItem \Magento\Sales\Api\Data\OrderItemInterface
     */
    private function restoreQuoteItem($orderItem)
    {
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (\Exception $e) {
                return;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = empty($info) ? [] : $info;
            $info = new \Magento\Framework\DataObject($info);
            $info->setQty($orderItem->getQtyOrdered());

            $quote = $this->quoteRepository->get($this->getQuoteId());

            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $quoteItem = $quote->addProduct($product, $info);
            $quoteItem->save();
            $quote->collectTotals()->save();
            $orderItem->setQuoteItemId($quoteItem->getItemId())->save();

            $this->updateChildOrderItems($quoteItem);
        }
    }

    /**
     * @param $quoteItem \Magento\Quote\Model\Quote\Item
     */
    private function updateChildOrderItems($quoteItem)
    {
        $childrenItems = $quoteItem->getChildren();
        foreach ($childrenItems as $childrenItem) {
            $orderItems = $this->getAllItems();
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() == $childrenItem->getProductId()) {
                    $orderItem->setQuoteItemId($childrenItem->getItemId())->save();
                    break;
                }
            }
        }
    }

    /**
     * @return void
     */
    private function syncAddresses()
    {
        if (!$this->getIsVirtual()) {
            $data = $this->getShippingAddress()->getData();
            $quoteAddress = $this->getQuote()->getShippingAddress();
            $addressId = $quoteAddress->getAddressId();
            if (!empty($addressId)) {
                $data['address_id'] = $addressId;
                $data['customer_id'] = $this->getCustomerId();
                $quoteAddress->setData($data);
                $quoteAddress->save();
            }
        }

        $data = $this->getBillingAddress()->getData();
        $quoteAddress = $this->getQuote()->getBillingAddress();
        $addressId = $quoteAddress->getAddressId();
        if (!empty($addressId)) {
            $data['address_id'] = $addressId;
            $data['customer_id'] = $this->getCustomerId();
            $quoteAddress->setData($data);
            $quoteAddress->save();
        }
    }

    /**
     * @return void
     */
    private function addCustomPriceToOrderGrandTotal()
    {
        /**
         * Add custom logic if you want add custom price to order
         * $additional_total = 0.0;        // add custom amount
         * $additional_base_total = 0.0;   // add custom base amount
         *
         * $grandTotal = $this->getGrandTotal();
         * $baseGrandTotal = $this->getBaseGrandTotal();
         * $this->setGrandTotal($grandTotal + $additional_total)
         *    ->setBaseGrandTotal($baseGrandTotal + $additional_base_total)
         *    ->save();
         */
    }

    /**
     * @return bool
     */
    public function isAllowEditOrder()
    {
        $allowedStatuses = $this->_scopeConfig->getValue('iwdordermanager/general/order_statuses');
        $allowedStatuses = explode(',', $allowedStatuses);
        return in_array($this->getStatus(), $allowedStatuses);
    }

    /**
     * @return bool
     */
    public function isAllowDeleteOrder()
    {
        $isAllowedDelete = $this->_scopeConfig->getValue('iwdordermanager/allow_delete/orders');

        if ($isAllowedDelete) {
            $allowedStatuses = $this->_scopeConfig->getValue('iwdordermanager/allow_delete/order_statuses');
            $allowedStatuses = explode(',', $allowedStatuses);
            return in_array($this->getStatus(), $allowedStatuses);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        $this->deleteRelatedShipments();
        $this->deleteRelatedInvoices();
        $this->deleteRelatedCreditMemos();
        $this->deleteRelatedOrderInfo();

        Logger::getInstance()->addLogIntoLogTable(__('Order was deleted'), null, $this->getIncrementId());

        return parent::beforeDelete();
    }

    /**
     * @return void
     */
    protected function deleteRelatedOrderInfo()
    {
        try {
            $collection = $this->_addressCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->_orderItemCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->_paymentCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->orderHistoryCollectionFactory->create()->setOrderFilter($this);
            foreach ($collection as $object) {
                $object->delete();
            }

            $collection = $this->taxCollectionFactory->create()->loadByOrder($this);
            foreach ($collection as $object) {
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedInvoices()
    {
        try {
            $collection = $this->getInvoiceCollection();
            foreach ($collection as $item) {
                $object = $this->invoice->load($item->getId());
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedShipments()
    {
        try {
            $collection = $this->getShipmentsCollection();
            foreach ($collection as $item) {
                $object = $this->shipment->load($item->getId());
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedCreditMemos()
    {
        try {
            $collection = $this->getCreditmemosCollection();
            foreach ($collection as $item) {
                $object = $this->creditmemo->load($item->getId());
                $object->delete();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @param string $status
     * @return void
     */
    public function updateOrderStatus($status)
    {
        $oldStatus = $this->getStatus();
        $newStatus = $status;

        $this->setData('status', $status)->save();

        if ($oldStatus != $newStatus) {
            $log = __('Order status has been changed from "%1" to "%2"', $oldStatus, $newStatus);

            Logger::getInstance()->addLogIntoLogTable($log, $this->getId(), $this->getIncrementId());
            Logger::getInstance()->addChange('Status', $oldStatus, $newStatus);
            Logger::getInstance()->saveLogsAsOrderComments($this);
        }
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        $customerId = $this->getCustomerId();
        try {
            return $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            $customer = $this->customerDataFactory->create();
            $customer->setId(null);
            return $customer;
        }
    }

    /**
     * @return \IWD\OrderManager\Model\Quote\Quote
     */
    public function getQuote()
    {
        if (empty($this->quote->getId())) {
            $quoteId = $this->getQuoteId();
            $this->quote->load($quoteId);
        }
        return $this->quote;
    }
}
