<?php

namespace IWD\AuthCIM\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class AbstractHandler
 * @package IWD\AuthCIM\Gateway\Response
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var ParseResponse
     */
    private $parseResponse;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $directResponse;

    /**
     * @var array
     */
    private $handlingSubject;

    /**
     * @var \Magento\Sales\Model\Order\Payment
     */
    private $payment = null;

    /**
     * @var \Magento\Payment\Gateway\Data\OrderAdapterInterface
     */
    private $order = null;

    /**
     * @param ParseResponse $parseResponse
     */
    public function __construct(
        ParseResponse $parseResponse
    ) {
        $this->parseResponse = $parseResponse;
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

        $this->updateTransactionData();
    }

    /**
     * @return void
     */
    public function updateTransactionData()
    {
        $transactionId = $this->getResponse()->getTransactionId();
        $this->getPayment()->setTransactionId($transactionId);
        $this->getPayment()->setCcTransId($transactionId);
        $this->getPayment()->setAdditionalInformation('last_transaction_id', $transactionId);
        $this->getPayment()->setAdditionalInformation('auth_code', $this->getResponse()->getAuthCode());
        $this->getPayment()->setIsTransactionClosed($this->isTransactionClosed());
        $this->getPayment()->setShouldCloseParentTransaction($this->isParentTransactionClosed());
    }

    /**
     * @return bool
     */
    abstract public function isTransactionClosed();

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    abstract public function isParentTransactionClosed();

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        if ($this->payment == null) {
            $this->payment = $this->handlingSubject['payment']->getPayment();
        }

        return $this->payment;
    }

    /**
     * @return \Magento\Payment\Gateway\Data\OrderAdapterInterface
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $this->order = $this->handlingSubject['payment']->getOrder();
        }

        return $this->order;
    }

    /**
     * @param $handlingSubject
     */
    public function setHandlingSubject($handlingSubject)
    {
        if (!isset($handlingSubject['payment']) || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $this->handlingSubject = $handlingSubject;
    }

    /**
     * @return array
     */
    public function getHandlingSubject()
    {
        return $this->handlingSubject;
    }

    /**
     * @param $response
     */
    public function setResponse($response)
    {
        $this->directResponse = $this->parseResponse->parseResponse($response);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getResponse()
    {
        return $this->directResponse;
    }

    /**
     * @return bool
     */
    public function isExistingPayment()
    {
        return $this->getPayment()->getId() != null && $this->getPayment()->getTransId() == null;
    }
}
