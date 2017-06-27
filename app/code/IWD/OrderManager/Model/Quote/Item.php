<?php

namespace IWD\OrderManager\Model\Quote;

/**
 * Class Item
 * @package IWD\OrderManager\Model\Quote
 */
/**
 * Class Item
 * @package IWD\OrderManager\Model\Quote
 */
class Item extends \Magento\Quote\Model\Quote\Item
{
    /**
     * @var string
     */
    const PREFIX_ID = 'q';

    /**
     * Flag Is Need Validate Qty
     * @var bool
     */
    protected $_needValidateQty = true;

    /**
     * Setter for $needValidateQty
     * @param bool $isNeed
     * @return $this
     */
    public function setNeedValidateQty($isNeed)
    {
        $this->_needValidateQty = $isNeed;
        return $this;
    }

    /**
     * Getter for $needValidateQty
     * @return bool
     */
    public function getNeedValidateQty()
    {
        return $this->_needValidateQty;
    }

    /**
     * Declare quote item quantity
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $qty = $this->_prepareQty($qty);
        $oldQty = $this->_getData(self::KEY_QTY);
        $this->setData(self::KEY_QTY, $qty);

        if ($this->getNeedValidateQty()) {
            $this->_eventManager->dispatch('sales_quote_item_qty_set_after', ['item' => $this]);
        }

        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }

        if ($this->getUseOldQty()) {
            $this->setData(self::KEY_QTY, $oldQty);
        }

        return $this;
    }
}
