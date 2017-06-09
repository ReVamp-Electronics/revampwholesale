<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\Data\MultiStockInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class MultiStock extends AbstractExtensibleModel implements MultiStockInterface
{

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId()
    {
        return $this->_getData(self::STOCK_ID);
    }

    /**
     * Set stock identifier
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId)
    {
        $this->setData(self::STOCK_ID, $stockId);
        return $this;
    }

    /**
     * Retrieve stock name
     *
     * @return string
     */
    public function getStockName()
    {
        return $this->_getData(self::STOCK_NAME);
    }

    /**
     * Set stock name
     *
     * @param string $stockName
     * @return $this
     */
    public function setStockName($stockName)
    {
        $this->setData(self::STOCK_NAME, $stockName);
        return $this;
    }
}
