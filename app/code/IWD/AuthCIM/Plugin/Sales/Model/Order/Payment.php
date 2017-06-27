<?php

namespace IWD\AuthCIM\Plugin\Sales\Model\Order;

/**
 * Class Payment
 * @package IWD\AuthCIM\Plugin\Sales\Model\Order
 * @see \Magento\Sales\Model\Order\Payment
 */
class Payment
{
    /**
     * @var array
     */
    private $commentsWasAddedToTrx = [];

    /**
     * @param $subject
     * @param $proceed
     * @param $transaction
     * @param $message
     */
    public function aroundAddTransactionCommentsToOrder(
        \Magento\Sales\Model\Order\Payment $subject,
        $proceed,
        $transaction,
        $message
    ) {
        if (!$this->isCommentForTrxAdded($transaction)) {
            $proceed($transaction, $message);
        }
    }

    /**
     * @param $transaction
     * @return bool
     */
    private function isCommentForTrxAdded($transaction)
    {
        $trxId = is_object($transaction) ? $transaction->getHtmlTxnId() : $transaction;

        if (in_array($trxId, $this->commentsWasAddedToTrx)) {
            return true;
        }

        $this->commentsWasAddedToTrx[] = $trxId;
        return false;
    }
}
