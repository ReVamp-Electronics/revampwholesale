<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SourceInterface
 * @package IWD\MultiInventory\Api\Data
 * @api
 */
interface SourceInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_ID = 'stock_id';
    const WEBSITE_ID = 'website_id';
    const STOCK_NAME = 'stock_name';
    /**#@-*/

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId();

    /**
     * Set stock identifier
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId);

    /**
     * Retrieve stock name
     *
     * @return string
     */
    public function getStockName();

    /**
     * Set stock name
     *
     * @param string $stockName
     * @return $this
     */
    public function setStockName($stockName);

    /**
     * Retrieve website id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);
}
