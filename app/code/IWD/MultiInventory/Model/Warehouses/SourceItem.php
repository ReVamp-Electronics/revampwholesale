<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\Data\SourceItemInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class StockItem
 * @package IWD\MultiInventory\Model\Warehouses
 */
class SourceItem extends AbstractExtensibleModel implements SourceItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceItem');
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->_getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(self::STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->_getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsInStock()
    {
        return $this->_getData(self::IS_IN_STOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function getManageStock()
    {
        return $this->_getData(self::MANAGE_STOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->_getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        $this->setData(self::ITEM_ID, $itemId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        $this->setData(self::PRODUCT_ID, $productId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId)
    {
        $this->setData(self::STOCK_ID, $stockId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInStock($isInStock)
    {
        $this->setData(self::IS_IN_STOCK, $isInStock);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setManageStock($manageStock)
    {
        $this->setData(self::MANAGE_STOCK, $manageStock);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData(self::WEBSITE_ID, $websiteId);
        return $this;
    }
}
