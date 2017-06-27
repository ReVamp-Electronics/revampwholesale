<?php

namespace IWD\OrderManager\Model\Payment\Method;

use Magento\Framework\Model\AbstractModel;

/**
 * Class AbstractMethod
 * @package IWD\OrderManager\Model\Payment\Method
 */
abstract class AbstractMethod extends AbstractModel
{
    /**
     * Order
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * Setter For Order
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Getter For Order
     * @return \IWD\OrderManager\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    abstract public function reauthorize();

    /**
     * @param $amount
     * @return mixed
     */
    public function formatPrice($amount)
    {
        return $this->getOrder()->getBaseCurrency()->formatTxt($amount);
    }
}
