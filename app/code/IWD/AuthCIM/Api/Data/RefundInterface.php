<?php

namespace IWD\AuthCIM\Api\Data;

/**
 * Deferred Refund Interface.
 * Issue:
 *  - Authorize.net can not refund for not settled transaction.
 * Solution:
 *  - if it's partial refund: save unsettled transaction and try refund it late (run via cron)
 *  - if it's full refund: void (cancel) this transaction.
 *
 * @api
 */
interface RefundInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID         = 'id';
    const PAYMENT_ID = 'payment_id';
    const AMOUNT     = 'amount';
    /**#@-*/
    
    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Get payment id
     *
     * @return int|null
     */
    public function getPaymentId();
    
    /**
     * Get deferred refund amount
     *
     * @return float|int|null
     */
    public function getAmount();

    /**
     * Set id
     *
     * @param int $id
     * @return RefundInterface
     */
    public function setId($id);

    /**
     * Set payment id
     *
     * @param int $paymentId
     * @return RefundInterface
     */
    public function setPaymentId($paymentId);

    /**
     * Set deferred refund amount
     *
     * @param float|int $amount
     * @return RefundInterface
     */
    public function setAmount($amount);
}
