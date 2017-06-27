<?php

namespace IWD\OrderManager\Model\Order;

use Magento\Sales\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product;

/**
 * Class Converter
 * @package IWD\OrderManager\Model\Order
 */
class Converter extends AbstractModel
{
    /**
     * @var Item
     */
    private $orderItem;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var \IWD\OrderManager\Model\Quote\Item
     */
    private $quoteItem;

    /**
     * @var \IWD\OrderManager\Model\Quote\Quote
     */
    private $quote;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    private $quoteItemToOrderItem;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    private $quoteItemCollectionFactory;

    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * @var Product
     */
    private $product;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param Item $orderItem
     * @param Order $order
     * @param \IWD\OrderManager\Model\Quote\Item $quoteItem
     * @param \IWD\OrderManager\Model\Quote\Quote $quote
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \IWD\OrderManager\Model\Order\Item $orderItem,
        \IWD\OrderManager\Model\Order\Order $order,
        \IWD\OrderManager\Model\Quote\Item $quoteItem,
        \IWD\OrderManager\Model\Quote\Quote $quote,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem,
        \Magento\Store\Model\Store $store,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Product $product,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderItem = $orderItem;
        $this->order = $order;
        $this->quoteItem = $quoteItem;
        $this->quote = $quote;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->store = $store;
        $this->productHelper = $productHelper;
        $this->productRepository = $productRepository;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->product = $product;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $product
     * @param string $message
     * @return void
     */
    private function addError($product, $message)
    {
        if (empty($product)) {
            $this->errors[] = $message;
        } else {
            $this->errors[$product] = $message;
        }
    }

    /**
     * @param int $itemId
     * @param string[] $params
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function createNewOrderItem($itemId, $params)
    {
        $orderItem = $this->orderItem->load($itemId);

        $params = $this->prepareProductOptions($orderItem, $params);

        /** @var \IWD\OrderManager\Model\Quote\Item $quoteItem*/
        $quoteItem = $this->convertOrderItemToQuoteItem($orderItem, $params);

        $quoteItemId = $orderItem->getQuoteItemId();
        $quoteItem->setItemId($quoteItemId);
        $quoteItem->save();

        return $this->quoteItemToOrderItem->convert(
            $quoteItem,
            ['parent_item' => $orderItem]
        );
    }

    /**
     * @param int $quoteItemId
     * @param null $params
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convertQuoteItemToOrderItem($quoteItemId, $params = null)
    {
        $quoteItem = $this->loadQuoteItem($quoteItemId);

        if (!empty($params)) {
            $quote = $this->loadQuoteByQuoteItem($quoteItem);
            $quoteItem->setNeedValidateQty(false);
            $quoteItem = $quote->updateItem($quoteItem, $params);
            $quote->collectTotals();
        }

        $orderItem = $this->quoteItemToOrderItem->convert($quoteItem);
        $parentId = $quoteItem->getParentItemId();
        $parentItemId = $this->getParentItemIdForQuote($parentId);

        $orderItem->setOriginalPrice($orderItem->getPrice());
        $orderItem->setBaseOriginalPrice($orderItem->getBasePrice());
        $orderItem->setParentItemId($parentItemId);

        return $orderItem;
    }

    /**
     * @param $quoteParentItemId
     * @return int|null
     */
    private function getParentItemIdForQuote($quoteParentItemId)
    {
        try {
            if (!empty($quoteParentItemId)) {
                /** @var $orderItem \IWD\OrderManager\Model\Order\Item */
                $orderItem = $this->orderItem->getCollection()
                    ->addFieldToFilter('quote_item_id', $quoteParentItemId)
                    ->getFirstItem();

                return $orderItem->getItemId();
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * @param int $quoteItemId
     * @return \IWD\OrderManager\Model\Quote\Item
     * @throws \Exception
     */
    private function loadQuoteItem($quoteItemId)
    {
        $quoteItem = $this->quoteItem->load($quoteItemId);

        $quote = $this->loadQuoteByQuoteItem($quoteItem);

        //can not return this $quoteItem instance, because it has not options
        $quoteItems = $this->quoteItemCollectionFactory->create()->setQuote($quote);
        $quoteItems->load();

        foreach ($quoteItems as $quoteItem) {
            /** @var \IWD\OrderManager\Model\Quote\Item $quoteItem */
            if ($quoteItem->getItemId() == $quoteItemId) {
                return $quoteItem;
            }
        }

        throw new LocalizedException(__('Can not load quote item'));
    }

    /**
     * @param string[] $params
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return \IWD\OrderManager\Model\Order\Item[]
     */
    public function createNewOrderItems($params, $order)
    {
        $params = $this->prepareParams($params);
        $quoteItems = $this->prepareNewQuoteItems($params, $order);

        $orderItems = [];
        foreach ($quoteItems as $quoteItem) {
            try {
                $orderItem = $this->quoteItemToOrderItem->convert($quoteItem);
                $orderItem->setItemId($quoteItem->getItemId());

                if ($quoteItem->getProductType() == 'bundle') {
                    $simpleOrderItems = $this->addSimpleItemsForBundle($quoteItem, $orderItem);
                    $orderItem->setChildrenItems($simpleOrderItems);
                }

                $orderItem->setMessage($quoteItem->getMessage());
                $orderItem->setHasError($quoteItem->getHasError());
                $orderItems[] = $orderItem;
            } catch (\Exception $e) {
                $this->addError($quoteItem->getName(), $e->getMessage());
            }
        }

        return $orderItems;
    }

    /**
     * @param \IWD\OrderManager\Model\Quote\Item $quoteItem
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    private function addSimpleItemsForBundle($quoteItem, $orderItem)
    {
        $simpleOrderItems = [];
        $simpleQuoteItems = $quoteItem->getChildren();
        foreach ($simpleQuoteItems as $simpleQuoteItem) {
            /** @var $simpleQuoteItem \Magento\Quote\Model\Quote\Item */
            try {
                $simpleOrderItem = $this->quoteItemToOrderItem->convert($simpleQuoteItem);
                $simpleOrderItem->setItemId($simpleQuoteItem->getItemId());
                $simpleOrderItem->setParentItem($orderItem);

                $simpleOrderItem->setMessage($simpleQuoteItem->getMessage());
                $simpleOrderItem->setHasError($simpleQuoteItem->getHasError());
                $simpleOrderItem->setDiscountPercent($quoteItem->getDiscountPercent());
                $simpleOrderItem->setTaxPercent($quoteItem->getTaxPercent());
                $simpleOrderItems[] = $simpleOrderItem;
            } catch (\Exception $e) {
                $this->addError($simpleQuoteItem->getName(), $e->getMessage());
            }
        }

        return $simpleOrderItems;
    }

    /**
     * @param string[] $params
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return \IWD\OrderManager\Model\Quote\Item []
     */
    private function prepareNewQuoteItems($params, $order)
    {
        $quoteItems = [];

        $quote = $this->loadQuoteByOrder($order);
        $quote->setAllItemsAreNew(true);

        foreach ($params as $productId => $options) {
            $product = $this->prepareProduct($productId, $order->getStore());
            try {
                $config = new DataObject($options);
                $quoteItem = $quote->addProduct($product, $config);
                if (is_string($quoteItem)) {
                    $errorMessage = $quoteItem;
                    $quoteItem = null;
                    throw new LocalizedException(__($errorMessage));
                }
            } catch (\Exception $e) {
                if (!empty($quoteItem)) {
                    $quoteItem = $quote->getLastErrorItem();
                    $quoteItem->setHasError(true);
                }
                $this->addError($product->getName(), $e->getMessage());
            }

            if (!empty($quoteItem)) {
                if (isset($options['bundle_option'])) {
                    $requestedOptions = count(
                        array_filter(
                            array_values($options['bundle_option']),
                            function ($value) {
                                return !empty($value) || $value === 0;
                            }
                        )
                    );

                    $addedOptions = count($quoteItem->getChildren());

                    if ($requestedOptions > $addedOptions) {
                        $quoteItem->setHasError(true);
                        $quoteItem->setMessage(
                            __('Not all selected products were added to the order as some products are currently unavailable.')
                        );
                    }
                }

                $quoteItem->save();
                $quoteItems[] = $quoteItem;
            }
        }

        $quote->collectTotals();//->save();
        return $quoteItems;
    }

    /**
     * @param int $productId
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Catalog\Model\Product
     * @throws \Exception
     */
    private function prepareProduct($productId, $store)
    {
         $this->product
            ->setStore($store)
            ->setStoreId($store->getStoreId())
            ->load($productId);

        if (!$this->product->getId()) {
            throw new LocalizedException(
                __('An issue occurred when trying to add product ID %1 to the order.', $productId)
            );
        }

        return $this->product;
    }

    /**
     * @param string[] $params
     * @return string[]
     */
    private function prepareParams($params)
    {
        $preparedParams = [];

        foreach ($params as $productId => $options) {
            if (isset($options['super_group'])) {
                foreach ($options['super_group'] as $id => $opt) {
                    if (is_string($opt) || is_numeric($opt)) {
                        $preparedParams[$id] = ['qty' => $opt];
                    }
                }
            } else {
                if (!empty($options)) {
                    $preparedParams[$productId] = $options;
                }
            }
        }

        return $preparedParams;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $orderItem
     * @param string[] $params
     * @return string[]
     */
    private function prepareProductOptions($orderItem, $params)
    {
        $params['product'] = $orderItem->getProductId();
        $params = $this->updateFiles($params, $orderItem->getItemId());
        return $params;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $orderItem
     * @param string[] $params
     * @return \Magento\Quote\Model\Quote\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function convertOrderItemToQuoteItem($orderItem, $params)
    {
        $quoteItemId = $orderItem->getQuoteItemId();

        $quoteItem = $this->quoteItem->load($quoteItemId);
        $quote = $this->loadQuoteByQuoteItem($quoteItem);

        $quoteItem->setQuote($quote);

        $qty = isset($params['qty']) && $params['qty'] > 0 ? $params['qty'] : 1;
        $isNeedValidateQty = (float)$orderItem->getQtyOrdered() < (float)$qty;

        $quoteItem->setNeedValidateQty($isNeedValidateQty);

        $quoteItem = $quote->updateItem($quoteItem, $params);

        $quote->collectTotals();

        return $quoteItem;
    }

    /**
     * @param  \IWD\OrderManager\Model\Quote\Item $quoteItem
     * @return \IWD\OrderManager\Model\Quote\Quote
     */
    private function loadQuoteByQuoteItem($quoteItem)
    {
        $storeId = $quoteItem->getStoreId();
        $store = $this->store->load($storeId);
        $quoteId = $quoteItem->getQuoteId();
        return $this->quote->setStore($store)->load($quoteId);
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return \IWD\OrderManager\Model\Quote\Quote
     */
    private function loadQuoteByOrder($order)
    {
        $storeId = $order->getStoreId();
        $store = $this->store->load($storeId);
        $quoteId = $order->getQuoteId();
        return $this->quote->setStore($store)->load($quoteId);
    }

    /**
     * @param string[] $options
     * @param int $itemId
     * @return string[]
     */
    private function updateFiles($options, $itemId)
    {
        return $options;
    }
}
