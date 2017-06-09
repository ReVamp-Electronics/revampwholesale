<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items;

use \Magento\Sales\Block\Adminhtml\Items\AbstractItems;

/**
 * Class AbstractType
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items
 */
class AbstractType extends AbstractItems
{
    /**
     * Calculate Child
     */
    const CALCULATE_CHILD = 0;

    /**
     * Calculate parent
     */
    const CALCULATE_PARENT = 1;

    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    protected $order = null;

    /**
     * @var \IWD\OrderManager\Model\Order\Item
     */
    protected $orderItem = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_adminHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
        $this->_adminHelper = $adminHelper;
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $orderItem
     * @return $this
     */
    public function setOrderItem($orderItem)
    {
        $this->orderItem = $orderItem;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item
     */
    public function getPriceDataObject()
    {
        return $this->getOrderItem();
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $item
     * @return void
     */
    public function initItem($item)
    {
        $type = $item->getProductType();
        $this->getItemRenderer($type);
    }

    /**
     * @param string $priceType
     * @return string
     */
    public function getPriceHtml($priceType)
    {
        $basePrice = $this->getOrderItem()->getData('base_' . $priceType);
        $price = $this->getOrderItem()->getData($priceType);

        return $this->_adminHelper->displayPrices(
            $this->getOrder(),
            $basePrice,
            $price,
            false,
            '<br/>'
        );
    }

    /**
     * @param string $priceType
     * @return string
     */
    public function getPrice($priceType)
    {
        $price = $this->getOrderItem()->getData($priceType);
        return number_format($price, 2, '.', '');
    }

    /**
     * @param string $percentType
     * @return string
     */
    public function getPercent($percentType)
    {
        $percent = $this->getOrderItem()->getData($percentType);
        return number_format($percent, 2, '.', '');
    }

    /**
     * @param string $percentType
     * @return string
     */
    public function getPercentHtml($percentType)
    {
        return $this->getPercent($percentType) . "%";
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item|null $orderItem
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockObjectForOrderItem($orderItem = null)
    {
        if ($orderItem == null) {
            $orderItem = $this->getOrderItem();
        }

        if ($orderItem->getProductType() == 'configurable') {
            $childOrderItem = $this->_objectManager->create('IWD\OrderManager\Model\Order\Item')
                ->getCollection()
                ->addFieldToFilter('parent_item_id', $orderItem->getItemId())
                ->getFirstItem();

            if (!empty($childOrderItem)) {
                $orderItem = $childOrderItem;
            }
        }

        $stockItem = $this->stockRegistry->getStockItem(
            $orderItem->getProductId(),
            $orderItem->getStore()->getWebsiteId()
        );

        return $stockItem;
    }

    /**
     * @return string
     */
    public function getItemTotalHtml()
    {
        $basePrice = $this->getBaseItemTotal();
        $price = $this->getItemTotal();

        return $this->_adminHelper->displayPrices(
            $this->getOrder(),
            $basePrice,
            $price,
            false,
            '<br/>'
        );
    }

    /**
     * @return string
     */
    public function getBaseItemTotal()
    {
        $orderItem = $this->getOrderItem();

        $total = $orderItem->getBaseRowTotal()
            + $orderItem->getBaseTaxAmount()
            + $orderItem->getBaseWeeeTaxAppliedRowAmount()
            + $orderItem->getBaseDiscountTaxCompensationAmount()
            - $orderItem->getBaseDiscountAmount();

        return number_format($total, 2, '.', '');
    }

    /**
     * @return string
     */
    public function getItemTotal()
    {
        $orderItem = $this->getOrderItem();

        $total = $orderItem->getBaseRowTotal()
            + $orderItem->getTaxAmount()
            + $orderItem->getWeeeTaxAppliedRowAmount()
            + $orderItem->getDiscountTaxCompensationAmount()
            - $orderItem->getDiscountAmount();

        return number_format($total, 2, '.', '');
    }

    /**
     * @param null $orderItem
     * @return float|int
     */
    public function getItemQty($orderItem = null)
    {
        if ($orderItem == null) {
            $orderItem = $this->getOrderItem();
        }

        $itemQty = $orderItem->getQtyOrdered()
            - $orderItem->getQtyRefunded()
            - $orderItem->getQtyCanceled();

        return $itemQty < 0 ? 0 : $itemQty;
    }

    /**
     * @return float
     */
    public function getStockQty()
    {
        $stockQty = $this->getStockObjectForOrderItem()->getQty();
        return $stockQty + $this->getItemQty();
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return bool
     */
    public function isChildCalculated($item)
    {
        $options = $item->getProductOptions();
        return ($options
            && isset($options['product_calculations'])
            && $options['product_calculations'] == self::CALCULATE_CHILD
        );
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        return true;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigureButtonHtml()
    {
        $product = $this->getOrderItem()->getProduct();

        $options = ['label' => __('Configure')];
        if ($product->canConfigure()) {
            $id = $this->getPrefixId() . $this->getOrderItem()->getId();
            $options['class'] = sprintf("configure-order-item item-id-%s", $id);

            return $this->getLayout()
                ->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData($options)
                ->setDataAttribute(['order-item-id' => $id])
                ->toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getOptionsForProduct()
    {
        $options = $this->getChildBlock('iwdordermamager_order_item_options');

        if ($options) {
            $options->setOrderItem($this->getOrderItem());
            return $options->toHtml();
        }

        return '';
    }

    /**
     * @return int
     */
    public function getDefaultBackToStock()
    {
        return $this->_scopeConfig
            ->getValue('iwdordermanager/order_items/return_to_stock') ? 1 : 0;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param int $stockQty
     * @return int
     */
    public function isStockValidation($item, $stockQty)
    {
        $productType = $item->getProductType();
        $isVirtual = in_array($productType, ['downloadable', 'virtual']);

        return $isVirtual && empty($stockQty) ? 0 : 1;
    }

    /**
     * @return string
     */
    public function getOrderItemId()
    {
        return $this->getPrefixId() . $this->getOrderItem()->getItemId();
    }

    /**
     * @return string
     */
    public function getParentItemId()
    {
        $parentItem = $this->getOrderItem()->getParentItem();
        $parentId = !empty($parentItem) ? $parentItem->getItemId() : 0;

        return $this->getPrefixId() . $parentId;
    }

    /**
     * @return bool
     */
    public function hasOrderItemParent()
    {
        $parentItem = $this->getOrderItem()->getParentItem();
        return !empty($parentItem);
    }

    /**
     * @return string
     */
    public function getPrefixId()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getEditedItemType()
    {
        return 'order';
    }

    /**
     * @param $block
     * @return bool
     */
    public function isAllowedAction($block)
    {
        if ($block == 'items_edit' && $this->getEditedItemType() == 'quote') {
            return true;
        }

        return $this->_authorization
            ->isAllowed('IWD_OrderManager::iwdordermanager_' . $block);
    }
}
