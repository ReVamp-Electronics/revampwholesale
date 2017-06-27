<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Api\Data\RefundInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Refund
 * @package IWD\AuthCIM\Model
 */
class Refund extends AbstractModel implements RefundInterface
{
    /**
     * Initialization here
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('IWD\AuthCIM\Model\ResourceModel\Refund');
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get payment id
     *
     * @return int|null
     */
    public function getPaymentId()
    {
        return $this->getData(self::PAYMENT_ID);
    }

    /**
     * Get deferred refund amount
     *
     * @return float|int|null
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set id
     *
     * @param int $id
     * @return RefundInterface
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * Set payment id
     *
     * @param int $paymentId
     * @return RefundInterface
     */
    public function setPaymentId($paymentId)
    {
        $this->setData(self::PAYMENT_ID, $paymentId);
        return $this;
    }

    /**
     * Set deferred refund amount
     *
     * @param float|int $amount
     * @return RefundInterface
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
        return $this;
    }
}
