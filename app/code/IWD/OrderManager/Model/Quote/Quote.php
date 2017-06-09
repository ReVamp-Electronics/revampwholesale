<?php

namespace IWD\OrderManager\Model\Quote;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Quote extends \Magento\Quote\Model\Quote
{
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $lastErrorItem;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $quoteItem;

    /**
     * @var bool
     */
    private $allItemsAreNew;

    /**
     * @var Item
     */
    private $quoteItemObject;

    /**
     * Quote constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Model\Quote\PaymentFactory $quotePaymentFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory
     * @param DataObject\Copy $objectCopyService
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Quote\Model\Quote\Item\Processor $itemProcessor
     * @param DataObject\Factory $objectFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param \Magento\Quote\Model\Quote\TotalsReader $totalsReader
     * @param \Magento\Quote\Model\ShippingFactory $shippingFactory
     * @param \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory
     * @param Item $quoteItemObject
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory,
        \IWD\OrderManager\Model\Quote\Item $quoteItemObject,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->allItemsAreNew = false;
        $this->quoteItemObject = $quoteItemObject;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $quoteValidator,
            $catalogProduct,
            $scopeConfig,
            $storeManager,
            $config,
            $quoteAddressFactory,
            $customerFactory,
            $groupRepository,
            $quoteItemCollectionFactory,
            $quoteItemFactory,
            $messageFactory,
            $statusListFactory,
            $productRepository,
            $quotePaymentFactory,
            $quotePaymentCollectionFactory,
            $objectCopyService,
            $stockRegistry,
            $itemProcessor,
            $objectFactory,
            $addressRepository,
            $criteriaBuilder,
            $filterBuilder,
            $addressDataFactory,
            $customerDataFactory,
            $customerRepository,
            $dataObjectHelper,
            $extensibleDataObjectConverter,
            $currencyFactory,
            $extensionAttributesJoinProcessor,
            $totalsCollector,
            $totalsReader,
            $shippingFactory,
            $shippingAssignmentFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\Framework\DataObject $buyRequest
     * @param null|array|\Magento\Framework\DataObject $params
     * @return \Magento\Quote\Model\Quote\Item
     * @throws LocalizedException
     *
     * @see \Magento\Catalog\Helper\Product::addParamsToBuyRequest()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateItem($item, $buyRequest, $params = null)
    {
        if (!$item) {
            throw new LocalizedException(__('This is the wrong quote item id to update configuration.'));
        }

        $this->quoteItem = $item;
        $itemId = $item->getId();

        $productId = $item->getProduct()->getId();

        $product = clone $this->productRepository->getById($productId, false, $this->getStore()->getId());

        if (empty($params)) {
            $params = new DataObject();
        } elseif (is_array($params)) {
            $params = new DataObject($params);
        }

        $params->setCurrentConfig($item->getBuyRequest());
        $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);
        $buyRequest->setResetCount(true);
        $resultItem = $this->addProduct($product, $buyRequest);

        if (is_string($resultItem)) {
            throw new LocalizedException(__($resultItem));
        }

        if ($resultItem->getParentItem()) {
            $resultItem = $resultItem->getParentItem();
        }

        if ($resultItem->getId() != $itemId) {
            $this->removeItem($itemId);

            $items = $this->getAllItems();
            foreach ($items as $item) {
                if ($item->getProductId() == $productId
                    && $item->getId() != $resultItem->getId()
                    && $resultItem->compare($item)
                ) {
                    $qty = $resultItem->getQty() + $item->getQty();
                    $resultItem->setQty($qty);
                    $this->removeItem($item->getId());
                    break;
                }
            }
        } else {
            $resultItem->setQty($buyRequest->getQty());
        }

        return $resultItem;
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }
        if (is_array($request)) {
            $request = $this->objectFactory->create($request);
        }
        if (!$request instanceof DataObject) {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
            return strval($cartCandidates);
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $this->lastErrorItem = null;
        $parentItem = null;
        $errors = [];
        $item = null;
        $items = [];
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->getItemByProduct($candidate);
            if (!$item) {
                $item = $this->initQuoteItemObject($candidate, $request);
                // Add only item that is not in quote already
                $this->addItem($item);
            }

            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            $item->setData(CartItemInterface::KEY_QTY, 0);
            $this->itemProcessor->prepare($item, $request, $candidate);

            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) {
                    // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }

        if (!empty($errors)) {
            $this->lastErrorItem = $item;
            throw new LocalizedException(__(implode("\n", $errors)));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
        return $parentItem;
    }

    /**
     * Retrieve quote item by product id
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  \Magento\Quote\Model\Quote\Item|bool
     */
    public function getItemByProduct($product)
    {
        if ($this->allItemsAreNew) {
            parent::getItemByProduct($product);
            return false;
        }
        return parent::getItemByProduct($product);
    }

    /**
     * Initialize quote item object
     *
     * @param \Magento\Catalog\Helper\Product $product
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function initQuoteItemObject($product, $request)
    {
        $quoteItemObject = clone $this->quoteItemObject;

        if ($this->quoteItem) {
            $quoteItemObject->setStoreId($this->quoteItem->getStoreId());
        } else {
            $quoteItemObject->setStoreId($this->getStoreId());
        }

        $quoteItemObject->setOptions($product->getCustomOptions());
        $quoteItemObject->setProduct($product);

        if ($request->getResetCount()
            && !$product->getStickWithinParent()
            && $quoteItemObject->getId() === $request->getId()
        ) {
            $quoteItemObject->setData(CartItemInterface::KEY_QTY, 0);
        }

        return $quoteItemObject;
    }

    /**
     * @param bool $allItemsAreNew
     * @return $this
     */
    public function setAllItemsAreNew($allItemsAreNew)
    {
        $this->allItemsAreNew = $allItemsAreNew;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getLastErrorItem()
    {
        return $this->lastErrorItem;
    }
}
