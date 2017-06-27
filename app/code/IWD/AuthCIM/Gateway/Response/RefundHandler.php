<?php

namespace IWD\AuthCIM\Gateway\Response;

use IWD\AuthCIM\Model\Refund as DeferredRefund;
use IWD\AuthCIM\Model\RefundRepository;
use IWD\AuthCIM\Model\VoidTransaction;
use IWD\AuthCIM\Model\Method;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class RefundHandler
 * @package IWD\AuthCIM\Gateway\Response
 */
class RefundHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var VoidTransaction
     */
    private $voidTransaction;

    /**
     * @var DeferredRefund
     */
    private $deferredRefund;

    /**
     * @var RefundRepository
     */
    private $refundRepository;

    /**
     * @var Method
     */
    private $method;

    /**
     * RefundHandler constructor.
     *
     * @param ParseResponse $parseResponse
     * @param VoidTransaction $voidTransaction
     * @param DeferredRefund $deferredRefund
     * @param RefundRepository $refundRepository
     * @param Method $method
     */
    public function __construct(
        ParseResponse $parseResponse,
        VoidTransaction $voidTransaction,
        DeferredRefund $deferredRefund,
        RefundRepository $refundRepository,
        Method $method
    ) {
        parent::__construct($parseResponse);
        $this->voidTransaction = $voidTransaction;
        $this->refundRepository = $refundRepository;
        $this->deferredRefund = $deferredRefund;
        $this->method = $method;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->setHandlingSubject($handlingSubject);
        $this->setResponse($response);

        if ($this->isUnsettledTransactionYet()) {
            if ($this->isPartialInvoiceRefund()) {
                $this->saveRefundForTryingLate();
            } else {
                $this->voidTransactionInsteadRefund();
            }
        } else {
            $this->updateTransactionData();
        }
    }

    /**
     * @return bool
     */
    private function isUnsettledTransactionYet()
    {
        return $this->getResponse()->getResponseReasonCode() == 54;
    }

    /**
     * @return bool
     */
    private function isPartialInvoiceRefund()
    {
        $amount = $this->getResponse()->getAmount();

        $invoiceAmount = 0;
        if ($this->getPayment()->getCreditmemo()) {
            $invoiceAmount = $this->getPayment()->getCreditmemo()->getInvoice()->getBaseGrandTotal();
        }

        return $amount != $invoiceAmount;
    }

    /**
     * Authorize.net can not refund for not settled transaction.
     * We save refund transaction and try refund it late via cron
     *
     * @return void
     */
    private function saveRefundForTryingLate()
    {
        $paymentId = $this->getPayment()->getId();
        $amount = $this->getResponse()->getAmount();

        $this->method->saveRefundForTryingLate($paymentId, $amount);
    }

    /**
     * If it's full invoice refund and transaction does not settled yet
     * we can void transaction instead refund. For customer it will be same.
     *
     * @return void
     */
    private function voidTransactionInsteadRefund()
    {
        $this->voidTransaction->voidTransaction($this->getPayment(), $this->getOrder());
    }

    /**
     * {@inheritdoc}
     */
    public function isTransactionClosed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isParentTransactionClosed()
    {
        try {
            $creditmemo = $this->getPayment()->getCreditmemo();
            if ($creditmemo == null) {
                return false;
            }

            $invoice = $creditmemo->getInvoice();
            if ($invoice == null) {
                return false;
            }

            return !(bool)$invoice->canRefund();
        } catch (\Exception $e) {
            return false;
        }
    }
}
