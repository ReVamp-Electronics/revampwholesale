<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Sales\Controller\Adminhtml\Order\Create;
use IWD\OrderManager\Model\Quote\Item;

/**
 * Class Options
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Items
 */
class Options extends Create
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_items_edit';

    /**
     * @var \IWD\OrderManager\Model\Order\Item $item
     */
    private $product;

    /**
     * @var \IWD\OrderManager\Model\Order\Converter $orderConverter
     */
    private $orderConverter;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \IWD\OrderManager\Model\Order\Converter $orderConverter
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \IWD\OrderManager\Model\Order\Converter $orderConverter
    ) {
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);

        $this->stockRegistry = $stockRegistry;
        $this->orderConverter = $orderConverter;
        $this->session = $context->getSession();
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $updateResult = new DataObject();
        /** @var \IWD\OrderManager\Model\Order\Item $orderItem */

        try {
            $orderItemId = $this->getRequest()->getParam('id');
            $params = $this->getRequest()->getParams();

            $prefixIdLength = strlen(Item::PREFIX_ID);

            if (substr($orderItemId, 0, $prefixIdLength) == Item::PREFIX_ID) {
                $quoteItemId = substr($orderItemId, $prefixIdLength, strlen($orderItemId));
                $orderItem = $this->orderConverter
                    ->convertQuoteItemToOrderItem($quoteItemId, $params);
            } else {
                $orderItem = $this->orderConverter
                    ->createNewOrderItem($orderItemId, $params);
                $orderItem->setId($orderItemId);
            }

            $resultPage = $this->resultPageFactory->create();
            /** @var \IWD\OrderManager\Block\Adminhtml\Order\Items\Options $optionsBlock */
            $optionsBlock = $resultPage->getLayout()
                ->getBlock('iwdordermamager_order_item_options');
            if (!empty($optionsBlock)) {
                $optionsHtml = $optionsBlock
                    ->setOrderItem($orderItem)
                    ->toHtml();

                $updateResult->setOptionsHtml($optionsHtml);
            }

            $options = serialize($orderItem->getData('product_options'));
            $updateResult->setProductOptions($options);

            $updateResult->setPrice($orderItem->getData('base_price'));
            $updateResult->setName($orderItem->getData('name'));
            $updateResult->setSku($orderItem->getData('sku'));
            $updateResult->setItemId($orderItemId);

            $stock = $this->getStockObjectForOrderItem($orderItem);
            $updateResult->setStock($stock);

            $updateResult->setOk(true);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        }

        $jsVarName = $this->getRequest()->getParam('as_js_varname');
        $updateResult->setJsVarName($jsVarName);

        $this->session->setCompositeProductResult($updateResult);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('catalog/product/showUpdateResult');
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $orderItem
     * @return float[]
     */
    public function getStockObjectForOrderItem($orderItem)
    {
        if ($orderItem->getProductType() == 'configurable') {
            $simpleSku = $orderItem->getSku();
            $options = $orderItem->getData('product_options');
            if (isset($options['simple_sku'])) {
                $simpleSku = $options['simple_sku'];
            }
            $stock = $this->stockRegistry->getStockItemBySku(
                $simpleSku,
                $orderItem->getStore()->getWebsiteId()
            );
        } else {
            $simpleId = $orderItem->getProductId();
            $stock = $this->stockRegistry->getStockItem(
                $simpleId,
                $orderItem->getStore()->getWebsiteId()
            );
        }

        $stockQtyIncrements = $stock->getQtyIncrements();
        $stockQty = $stock->getQty();

        return [
            'data-stock-validate' => $this->isStockValidation($orderItem, $stockQty),
            'data-stock-qty-increment' => $stockQtyIncrements ? $stockQtyIncrements : 1,
            'data-stock-qty' => $stockQty ? $stockQty : 1,
            'data-stock-qty-min' => $stock->getMinQty() ? $stock->getMinQty() : 1,
            'data-stock-min-sales-qty' => $stock->getMinSaleQty() ? $stock->getMinSaleQty() : 1,
            'data-stock-max-sales-qty' => $stock->getMaxSaleQty() ? $stock->getMaxSaleQty() : 1,
        ];
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param int $stockQty
     * @return bool
     */
    public function isStockValidation($item, $stockQty)
    {
        $productType = $item->getProductType();
        $isVirtual = in_array($productType, ['downloadable', 'virtual']);

        return $isVirtual && empty($stockQty) ? '0' : '1';
    }
}
