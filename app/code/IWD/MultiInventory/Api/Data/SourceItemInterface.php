<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SourceItemInterface
 * @package IWD\MultiInventory\Api\Data
 * @api
 */
interface SourceItemInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ITEM_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const STOCK_ID = 'stock_id';
    const QTY = 'qty';
    const IS_IN_STOCK = 'is_in_stock';
    const MANAGE_STOCK = 'manage_stock';
    const WEBSITE_ID = 'website_id';
    /**#@-*/

    /**
     * Retrieve item id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Retrieve product id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Retrieve stock id
     *
     * @return int
     */
    public function getStockId();

    /**
     * Retrieve qty
     *
     * @return double
     */
    public function getQty();

    /**
     * Retrieve is in stock
     *
     * @return int
     */
    public function getIsInStock();

    /**
     * Retrieve manage stock
     *
     * @return int
     */
    public function getManageStock();

    /**
     * Retrieve website id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Set product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Set stock id
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId);

    /**
     * Set qty
     *
     * @param double $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Set is in stock
     *
     * @param int $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);

    /**
     * Set manage stock
     *
     * @param int $manageStock
     * @return $this
     */
    public function setManageStock($manageStock);

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);
}
