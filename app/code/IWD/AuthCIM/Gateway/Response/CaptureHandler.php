<?php

namespace IWD\AuthCIM\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Api\TransactionRepositoryInterface as TransactionRepository;

/**
 * Class CaptureHandler
 * @package IWD\AuthCIM\Gateway\Response
 */
class CaptureHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * CaptureHandler constructor.
     * @param ParseResponse $parseResponse
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(
        ParseResponse $parseResponse,
        TransactionRepository $transactionRepository
    ) {
        parent::__construct($parseResponse);
        $this->transactionRepository = $transactionRepository;
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTransactionData()
    {
        parent::updateTransactionData();

        $transactionId = $this->getResponse()->getTransactionId();
        $this->getPayment()->setParentTransactionId($transactionId);

        if ($this->isExistingPayment()) {
            if (isset($this->getHandlingSubject()['amount'])) {
                $amount = $this->getHandlingSubject()['amount'];
                $formatAmount = $this->formatPrice($amount);
                $message = $this->getPayment()->prependMessage(__('Captured amount of %1 online.', $formatAmount));
            } else {
                $message = __('Captured amount online.');
            }

            $transaction = $this->getPayment()->addTransaction(Transaction::TYPE_CAPTURE, null, true);
            $this->getPayment()->addTransactionCommentsToOrder($transaction, $message);

            $this->transactionRepository->save($transaction);
        }
    }

    /**
     * @param $amount
     * @return string
     */
    private function formatPrice($amount)
    {
        return $this->getPayment()->getOrder()->getBaseCurrency()->formatTxt($amount);
    }
}
