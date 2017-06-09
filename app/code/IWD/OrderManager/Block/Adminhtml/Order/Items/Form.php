<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items;

use Magento\Tax\Model\Config;
use Magento\Catalog\Model\Product\Type;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var \IWD\OrderManager\Model\Order\Order $order
     */
    private $order = null;

    /**
     * @var \Magento\Tax\Model\Config $taxConfig
     */
    private $taxConfig = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->taxConfig = $taxConfig;
        parent::__construct($context, $data);
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
     * @return string
     */
    public function getItemsForm()
    {
        $orderItems = $this->getItems();
        $formHtml = "";

        foreach ($orderItems as $item) {
            $productType = $item->getProductType();
            $child = ($item->getParentItemId() != null);

            switch ($productType) {
                case $child:
                    break;
                case Type::TYPE_BUNDLE:
                    $formHtml .= $this->getBundleForm($item);
                    break;
                default:
                    $formHtml .= $this->getItemForm($item, 'iwdordermamager_order_simple_item_form');
            }
        }

        return $formHtml;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getItems()
    {
        return $this->getOrder()->getItems();
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $orderItem
     * @return string
     */
    public function getBundleForm($orderItem)
    {
        $formHtml = $this->getItemForm($orderItem, 'iwdordermamager_order_bundle_item_form');

        $childItems = $orderItem->getChildrenItems();
        $_prevOptionId = '';

        /**
         * @var \IWD\OrderManager\Model\Order\Item $item
         */
        foreach ($childItems as $item) {
            $attributes = $this->getSelectionAttributes($item);
            if ($item->getParentItem() && $_prevOptionId != $attributes['option_id']) {
                $item->setOptionLabel($attributes['option_label']);
                $_prevOptionId = $attributes['option_id'];
            }

            $formHtml .= $this->getItemForm($item, 'iwdordermamager_order_simple_item_form');
        }

        return $formHtml;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $item
     * @return mixed|null
     */
    private function getSelectionAttributes($item)
    {
        $options = $item->getProductOptions();
        if (isset($options['bundle_selection_attributes'])) {
            return unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $orderItem
     * @param string $block
     * @return string
     */
    public function getItemForm($orderItem, $block)
    {
        $itemForm = $this->getChildBlock($block);
        if ($itemForm) {
            $itemForm->setOrderItem($orderItem);
            $itemForm->setOrder($this->getOrder());

            return $itemForm->toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getActionsForm()
    {
        $actionsForm = $this->getChildBlock('iwdordermamager_order_actions');
        if ($actionsForm) {
            return $actionsForm->toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function jsonParams()
    {
        $data = [
            'taxCalculationMethodBasedOn' => $this->taxConfig->getAlgorithm(),
            'taxCalculationBasedOn' => $this->getTaxBasedOn() ? 1 : 0,
            'catalogPrices' => $this->taxConfig->priceIncludesTax() ? 1 : 0,
            'shippingPrices' => $this->taxConfig->shippingPriceIncludesTax() ? 1 : 0,
            'applyTaxAfterDiscount' => $this->taxConfig->applyTaxAfterDiscount() ? 1 : 0,
            'discountTax' => $this->taxConfig->discountTax() ? 1 : 0,
            'configureQuoteItemsUrl' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/configureQuoteItems'),
            'configureConfirmUrl' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/options'),
            'searchProductsUrl' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/search'),
            'validateStockQty' => $this->getValidateStockQty()
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonSearchParams()
    {
        $data = [
            'searchProductsUrl' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/search'),
            'addProductsUrl' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/add'),
            'baseUrl' => $this->_urlBuilder->getUrl('sales/order_create/loadBlock'),
            'removeQuoteItemUrl' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/removeQuoteItem'),
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    private function getTaxBasedOn()
    {
        return $this->_scopeConfig->getValue(
            Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    private function getValidateStockQty()
    {
        return $this->_scopeConfig->getValue('iwdordermanager/order_items/validate_inventory') ? 1 : 0;
    }
}
