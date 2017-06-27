<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use Magento\Sales\Controller\Adminhtml\Order\Create;
use Magento\Framework\DataObject;
use IWD\OrderManager\Model\Quote\Item;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use Magento\Catalog\Helper\Product\Composite as ProductComposite;
use IWD\OrderManager\Model\Order\Item as OrderItem;

/**
 * Class ConfigureQuoteItems
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Items
 */
class ConfigureQuoteItems extends Create
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_items_edit';

    /**
     * @var DataObject
     */
    private $configureResult;

    /**
     * @var QuoteItemOption
     */
    private $quoteItemOption;

    /**
     * @var QuoteItem
     */
    private $quoteItem;

    /**
     * @var ProductComposite
     */
    private $productComposite;

    /**
     * @var OrderItem
     */
    private $orderItem;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param QuoteItemOption $quoteItemOption
     * @param QuoteItem $quoteItem
     * @param ProductComposite $productComposite
     * @param OrderItem $orderItem
     * @param DataObject $configureResult
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        QuoteItemOption $quoteItemOption,
        QuoteItem $quoteItem,
        ProductComposite $productComposite,
        OrderItem $orderItem,
        DataObject $configureResult
    ) {
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
        $this->configureResult = $configureResult;
        $this->quoteItemOption = $quoteItemOption;
        $this->quoteItem = $quoteItem;
        $this->productComposite = $productComposite;
        $this->orderItem = $orderItem;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        try {
            $quoteItem = $this->getQuoteItem();
            $quoteItemId = $quoteItem->getItemId();

            $this->configureResult->setOk(true);

            $options = $this->quoteItemOption->getCollection()
                ->addItemFilter([$quoteItemId])
                ->getOptionsByItem($quoteItem);
            $quoteItem->setOptions($options);

            $this->configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $this->configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $this->configureResult->setProductId($quoteItem->getProductId());
        } catch (\Exception $e) {
            $this->configureResult->setError(true);
            $this->configureResult->setMessage($e->getMessage());
        }

        return $this->productComposite->renderConfigureResult($this->configureResult);
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item
     * @throws \Exception
     */
    private function getQuoteItem()
    {
        $orderItemId = $this->getRequest()->getParam('id');
        if (!$orderItemId) {
            throw new LocalizedException(__('Order item id is not received.'));
        }

        $prefixIdLength = strlen(Item::PREFIX_ID);
        if (substr($orderItemId, 0, $prefixIdLength) == Item::PREFIX_ID) {
            $quoteItemId = substr($orderItemId, $prefixIdLength, strlen($orderItemId));
        } else {
            $orderItem = $this->loadOrderItem($orderItemId);
            $quoteItemId = $orderItem->getQuoteItemId();
        }

        return $this->loadQuoteItem($quoteItemId);
    }

    /**
     * @param int $orderItemId
     * @return \IWD\OrderManager\Model\Order\Item
     * @throws \Exception
     */
    private function loadOrderItem($orderItemId)
    {
        /** @var \IWD\OrderManager\Model\Order\Item $orderItem */
        $orderItem = $this->orderItem->load($orderItemId);

        if (!$orderItem->getId()) {
            throw new LocalizedException(__('Order item is not loaded.'));
        }

        return $orderItem;
    }

    /**
     * @param int $quoteItemId
     * @return \Magento\Quote\Model\Quote\Item
     * @throws \Exception
     */
    private function loadQuoteItem($quoteItemId)
    {
        $quoteItem = $this->quoteItem->load($quoteItemId);
        if (!$quoteItem->getId()) {
            throw new LocalizedException(__('Quote item is not loaded.'));
        }

        return $quoteItem;
    }
}
