<?php

namespace IWD\AuthCIM\Model\Method;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface as TransactionRepository;
use Magento\Sales\Api\OrderPaymentRepositoryInterface as OrderPaymentRepository;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class AuthCim
 * @package IWD\AuthCIM\Model\Method
 */
class AuthCim extends Adapter
{
    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var string
     */
    private $formBlockType;

    /**
     * @var string
     */
    private $infoBlockType;

    /**
     * @var string
     */
    private $code;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandExecutor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * AuthCim constructor.
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param TransactionRepository $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        TransactionRepository $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        OrderPaymentRepository $orderPaymentRepository,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
        $this->valueHandlerPool = $valueHandlerPool;
        $this->validatorPool = $validatorPool;
        $this->commandPool = $commandPool;
        $this->code = $code;
        $this->infoBlockType = $infoBlockType;
        $this->formBlockType = $formBlockType;
        $this->eventManager = $eventManager;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandExecutor = $commandExecutor;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @inheritdoc
     */
    public function void(InfoInterface $payment)
    {
        $this->fetchTransactionsInfo($payment);
        $transactions = $this->loadTransactions($payment);

        foreach ($transactions as $transId => $transaction) {
            if (!$this->isTxnStatusAuthorized($transaction) && !$this->isTxnStatusCaptured($transaction)) {
                continue;
            }

            $transactionId = $transaction->getTxnId();
            $this->voidTransaction($payment, $transactionId);
        }

        return $this;
    }

    /**
     * @param $payment
     * @param $transactionId
     */
    private function addVoidTransactionAndComment($payment, $transactionId)
    {
        $payment->setAdditionalInformation('last_transaction_id', $transactionId);
        $payment->setTransactionId($transactionId . '-void');
        $payment->setParentTransactionId($transactionId);
        $payment->setIsTransactionClosed(true);
        $transaction = $payment->addTransaction(Transaction::TYPE_VOID, null, true);
        $message = $payment->prependMessage(__('Voided authorization.'));
        $payment->addTransactionCommentsToOrder($transaction, $message);
        $this->transactionRepository->save($transaction);
    }

    /**
     * @param $payment
     * @param $transactionId
     */
    public function voidTransaction($payment, $transactionId)
    {
        $payment->setTransId($transactionId);
        $this->executeCommand('void', ['payment' => $payment]);

        $this->addVoidTransactionAndComment($payment, $transactionId);
    }

    /**
     * @inheritdoc
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $transactions = $this->preparePaymentNetCIMTransactions($payment);
        $captured = array_sum($transactions['captured']);
        $settled = array_sum($transactions['settled']);

        if ($captured > $amount) {
            return $this->voidCapturedTransactions($payment, $transactions, $amount);
        } else {
            if ($captured + $settled < $amount) {
                throw new LocalizedException(__('We can not refund more than captured'));
            }

            return $this->voidCapturedAndRefundSettledTransactions($payment, $transactions, $amount);
        }
    }

    /**
     * @param $payment
     * @return array
     */
    private function preparePaymentNetCIMTransactions($payment)
    {
        $this->fetchTransactionsInfo($payment);

        $transactions = $this->loadTransactions($payment);

        $transactionsInfo = [
            'captured' => [],
            'settled' => []
        ];

        foreach ($transactions as $transaction) {
            $txnId = $this->prepareTransactionId($transaction);
            $trxInfo = $transaction->getAdditionalInformation(Transaction::RAW_DETAILS);

            $status = isset($trxInfo['transactionStatus']) ? $trxInfo['transactionStatus'] : false;
            if ($status == 'capturedPendingSettlement') {
                $transactionsInfo['captured'][$txnId] = $trxInfo['authAmount'];
            } elseif ($status == 'settledSuccessfully' && $transaction->getTxnType() == 'capture') {
                $transactionsInfo['settled'][$txnId] = $trxInfo['settleAmount'];
            }
        }

        return $transactionsInfo;
    }

    /**
     * @param $transaction
     * @return string
     */
    private function prepareTransactionId($transaction)
    {
        $txnId = $transaction->getTxnId();
        $delimiter = strpos($txnId, "-");
        return $delimiter ? substr($txnId, 0, $delimiter) : $txnId;
    }

    /**
     * @param $payment
     * @param $transactions
     * @param $refundAmount
     * @return $this
     * @throws LocalizedException
     */
    private function voidCapturedTransactions($payment, $transactions, $refundAmount)
    {
        foreach ($transactions['captured'] as $trxId => $trxAmount) {
            $this->voidTransaction($payment, $trxId);
            $this->savePayment($payment);

            $refundAmount -= $trxAmount;
            if ($refundAmount == 0) {
                $this->savePayment($payment);
                return $this;
            }

            if ($refundAmount < 0) {
                $captureAmount = abs($refundAmount);
                $this->captureTransaction($payment, $captureAmount);
                return $this;
            }
        }

        throw new LocalizedException(__('We can not refund more than captured'));
    }

    /**
     * @param $payment
     * @param $transactions
     * @param $refundAmount
     * @return $this
     * @throws LocalizedException
     */
    private function voidCapturedAndRefundSettledTransactions($payment, $transactions, $refundAmount)
    {
        // VOID ALL CAPTURED TRANSACTIONS
        foreach ($transactions['captured'] as $trxId => $trxAmount) {
            if ($refundAmount == 0) {
                return $this;
            }

            $this->voidTransaction($payment, $trxId);
            $refundAmount -= $trxAmount;
        }

        // REFUND SETTLED TRANSACTIONS
        foreach ($transactions['settled'] as $trxId => $trxAmount) {
            if ($refundAmount == 0) {
                $this->savePayment($payment);
                return $this;
            }

            if ($refundAmount < $trxAmount) {
                $refund = $refundAmount;
                $refundAmount = 0;
            } else {
                $refund = $trxAmount;
                $refundAmount -= $trxAmount;
            }

            $this->refundTransaction($payment, $refund, $trxId);
            $this->addRefundTransactionAndComment($payment, $refund, $trxId);
        }

        $this->savePayment($payment);
        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     */
    private function savePayment($payment)
    {
        $this->orderPaymentRepository->save($payment);
    }

    /**
     * @param $payment
     * @param $amount
     * @param $transactionId
     */
    public function refundTransaction($payment, $amount, $transactionId)
    {
        $payment->setTransId($transactionId);
        $this->executeCommand('refund', ['payment' => $payment, 'amount' => $amount]);
    }

    /**
     * @param $payment
     * @param $amount
     * @param $parentTransactionId
     */
    private function addRefundTransactionAndComment($payment, $amount, $parentTransactionId)
    {
        $transactionId = $payment->getAdditionalInformation('last_transaction_id');
        $payment->setTransactionId($transactionId);
        $payment->setParentTransactionId($parentTransactionId);
        $payment->setIsTransactionClosed(true);
        $transaction = $payment->addTransaction(Transaction::TYPE_REFUND, null, true);
        $message = $payment->prependMessage(__('Refunded %1.', $this->formatPrice($payment, $amount)));
        $payment->addTransactionCommentsToOrder($transaction, $message);
        $this->transactionRepository->save($transaction);
    }

    /**
     * @inheritdoc
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $transactions = $this->prepareCapture($payment, $amount);

        if (empty($transactions)) {
            $this->captureTransaction($payment, $amount);
            return $this;
        }

        foreach ($transactions as $transId => $transaction) {
            $amount = $transaction['authAmount'];
            if ($amount > 0) {
                $this->captureTransaction($payment, $amount, $transId);
            }
        }

        return $this;
    }

    /**
     * @param $payment
     * @param $amount
     * @param null $trxNumber
     */
    private function captureTransaction($payment, $amount, $trxNumber = null)
    {
        $payment->setTransId($trxNumber);
        $this->executeCommand('capture', ['payment' => $payment, 'amount' => $amount]);

        if ($trxNumber != null) {
            $this->addCaptureTransactionAndComment($payment, $amount, $trxNumber);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param $amount
     * @param $transactionId
     */
    private function addCaptureTransactionAndComment($payment, $amount, $transactionId)
    {
        $payment->setAdditionalInformation('last_transaction_id', $transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setParentTransactionId($transactionId);
        $payment->setIsTransactionClosed(true);

        /**
         * @var $transaction \Magento\Sales\Model\Order\Payment\Transaction
         */
        $transaction = $payment->addTransaction(Transaction::TYPE_CAPTURE, null, true);
        $message = $payment->prependMessage(__('Captured amount of %1 online.', $this->formatPrice($payment, $amount)));
        $payment->addTransactionCommentsToOrder($transaction, $message);
        $transaction->setOrderId($payment->getParentId())->setPaymentId($payment->getId());

        $this->transactionRepository->save($transaction);
    }

    /**
     * @param InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param $amount float
     * @return array|null
     */
    private function prepareCapture(InfoInterface $payment, $amount)
    {
        $this->fetchTransactionsInfo($payment);

        $transactions = $this->loadTransactions($payment);
        $amounts = $this->prepareTransactionAmountsForCapture($transactions, $amount);

        if (empty($amounts)) {
            return null;
        }

        $authTotal = 0.0;
        foreach ($amounts as $a) {
            $authTotal += $a['authAmount'];
        }

        // enough money authorized for capture
        if ($authTotal == $amount) {
            return $amounts;
        }

        // we should authorize/capture more money
        if ($authTotal < $amount) {
            $amount -= $authTotal;
            $this->captureTransaction($payment, $amount);

            return $amounts;
        }

        return [];
    }

    /**
     * @param TransactionInterface[] $transactions
     * @param $amount
     * @return array
     */
    private function prepareTransactionAmountsForCapture($transactions, $amount)
    {
        $startAmount = (float)$amount;
        $amounts = [];
        foreach ($transactions as $transaction) {
            $txnType = $transaction->getTxnType();

            if ($txnType != TransactionInterface::TYPE_AUTH || $transaction->getIsClosed() == 1) {
                continue;
            }

            if (!$this->isTxnStatusAuthorized($transaction)) {
                if ($this->isTxnStatusCaptured($transaction) && $txnType == TransactionInterface::TYPE_AUTH) {
                    $transaction->setTxnType(TransactionInterface::TYPE_CAPTURE)->setIsClosed(1);
                    $this->transactionRepository->save($transaction);
                }
                continue;
            }

            $transId = $transaction->getTxnId();

            $txnDetails = $this->getTransactionDetails($transaction);
            $allowedAmount = isset($txnDetails['authAmount']) ? $txnDetails['authAmount'] : 0;
            $startAmountTemp = (float)$startAmount - $allowedAmount;
            if ($startAmountTemp >= 0) {
                $amounts[$transId]["authAmount"] = $allowedAmount;
                $startAmount = $startAmountTemp;
            } elseif ($startAmountTemp < 0) {
                $amounts[$transId]["authAmount"] = $startAmount;
            }

            if ($startAmountTemp <= 0) {
                break;
            }
        }

        return $amounts;
    }

    /**
     * @param $transaction
     * @return array
     */
    private function getTransactionDetails($transaction)
    {
        $txnDetails = $transaction->getAdditionalInformation();

        return is_array($txnDetails) && isset($txnDetails[Transaction::RAW_DETAILS])
            ? $txnDetails[Transaction::RAW_DETAILS]
            : [];
    }

    /**
     * @param $transaction
     * @return mixed|null
     */
    private function getTransactionStatus($transaction)
    {
        $txnDetails = $this->getTransactionDetails($transaction);
        return isset($txnDetails['transactionStatus']) ? $txnDetails['transactionStatus'] : null;
    }

    /**
     * @param $transaction
     * @return bool
     */
    private function isTxnStatusAuthorized($transaction)
    {
        return $this->getTransactionStatus($transaction) == "authorizedPendingCapture";
    }

    /**
     * @param $transaction
     * @return bool
     */
    private function isTxnStatusCaptured($transaction)
    {
        return $this->getTransactionStatus($transaction) == "capturedPendingSettlement";
    }

    /**
     * @param InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    private function fetchTransactionsInfo(InfoInterface $payment)
    {
        $transactions = $this->loadTransactions($payment);
        foreach ($transactions as $transaction) {
            $payment->importTransactionInfo($transaction);
        }
    }

    /**
     * @param InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @return TransactionInterface[]
     */
    private function loadTransactions($payment)
    {
        $order = $payment->getOrder();

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField(TransactionInterface::ORDER_ID)
                    ->setValue($order->getId())
                    ->create(),
            ]
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->transactionRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @inheritdoc
     */
    private function executeCommand($commandCode, array $arguments = [])
    {
        if (!$this->canPerformCommand($commandCode)) {
            return null;
        }

        /** @var InfoInterface|null $payment */
        $payment = null;
        if (isset($arguments['payment']) && $arguments['payment'] instanceof InfoInterface) {
            $payment = $arguments['payment'];
            $arguments['payment'] = $this->paymentDataObjectFactory->create($arguments['payment']);
        }

        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->executeByCode($commandCode, $payment, $arguments);
        }

        if ($this->commandPool === null) {
            throw new \DomainException('Command pool is not configured for use.');
        }

        $command = $this->commandPool->get($commandCode);

        return $command->execute($arguments);
    }

    /**
     * Whether payment command is supported and can be executed
     *
     * @param string $commandCode
     * @return bool
     */
    private function canPerformCommand($commandCode)
    {
        return (bool)$this->getConfiguredValue('can_' . $commandCode);
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    private function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);
        $subject = [
            'field' => $field
        ];

        if ($this->getInfoInstance()) {
            $subject['payment'] = $this->paymentDataObjectFactory->create($this->getInfoInstance());
        }

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * @param $payment
     * @param $amount
     * @return mixed
     */
    public function formatPrice($payment, $amount)
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }
}
