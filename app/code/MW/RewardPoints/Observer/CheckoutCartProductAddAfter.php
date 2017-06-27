<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutCartProductAddAfter implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Helper\Rules
     */
    protected $_rulesHelper;

    /**
     * @var \MW\RewardPoints\Model\ProductsellpointFactory
     */
    protected $_productsellpointFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Helper\Rules $rulesHelper
     * @param \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Helper\Rules $rulesHelper,
        \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
    ) {
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
        $this->_rulesHelper = $rulesHelper;
        $this->_productsellpointFactory = $productsellpointFactory;
    }

    /**
     * Add points for product types after adding product to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product  = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();
        $product   = $this->_productFactory->create()->load($_product->getId());

        switch ($_product->getTypeId()) {
            case 'bundle':
                if (!$product->getData('mw_reward_point_sell_product')) {
                    // If product does not set sell point, then check sell for each item in product
                    // Get children products of bundle
                    $items = $this->_checkoutSession->getQuote()->getAllItems();
                    foreach ($items as $item) {
                        if ($item->getProductId() == $_product->getId()) {
                            foreach ($item->getChildren() as $bundleItem) {
                                $childProduct = $this->_productFactory->create()->load($bundleItem->getProductId());
                                $this->_rulesHelper->addCustomOptionPoint($childProduct, $bundleItem);
                            }
                        }
                    }
                } else {
                    $this->_rulesHelper->addCustomOptionPoint($product, $quoteItem);
                }
                break;
            case 'simple':
            case 'virtual':
            case 'downloadable':
                $this->_rulesHelper->addCustomOptionPoint($product, $quoteItem);
                break;
            case 'configurable':
                if (!$product->getData('mw_reward_point_sell_product')) {
                    // If product does not set sell point, then check sell for each item in product
                    // GET children products of configurable
                    $items = $this->_checkoutSession->getQuote()->getAllItems();
                    if ($info = $quoteItem->getProduct()->getCustomOption('info_buyRequest')) {
                        $infoArr = unserialize($info->getValue());
                    } else {
                        $infoArr = [];
                    }
                    $totalSellPoints = 0;
                    if (count($infoArr) > 0) {
                        $model = $this->_productsellpointFactory->create();
                        foreach ($infoArr['super_attribute'] as $attributeId => $value) {
                            $collection = $model->getCollection()
                                ->addFieldToFilter('product_id', $_product->getId())
                                ->addFieldToFilter('option_id', $value)
                                ->addFieldToFilter('option_type_id', $attributeId)
                                ->addFieldToFilter('type_id', 'super_attribute')
                                ->getFirstItem();

                            $totalSellPoints += intval($collection->getSellPoint());
                        }
                    }

                    foreach ($items as $item) {
                        if ($item->getProductId() == $_product->getId()) {
                            foreach ($item->getChildren() as $configurableItem) {
                                $childProduct = $this->_productFactory->create()->load($configurableItem->getProductId());
                                $this->_rulesHelper->addCustomOptionPoint($childProduct, $configurableItem, $totalSellPoints);
                            }
                        }
                    }
                } else {
                    $this->_rulesHelper->addCustomOptionPoint($product, $quoteItem);
                }
                break;
        }

        return $this;
    }
}
