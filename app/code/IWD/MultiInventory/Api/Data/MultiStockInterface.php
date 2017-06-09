<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * IWD Multi Interface Stock
 * @api
 */
interface MultiStockInterface extends ExtensibleDataInterface
{
    const STOCK_ID = 'stock_id';

    const STOCK_NAME = 'stock_name';

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
}
