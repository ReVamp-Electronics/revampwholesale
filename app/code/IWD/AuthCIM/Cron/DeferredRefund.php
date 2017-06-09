<?php

namespace IWD\AuthCIM\Cron;

use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use IWD\AuthCIM\Model\AbstractTransaction;
use IWD\AuthCIM\Model\Refund;
use IWD\AuthCIM\Model\RefundRepository;
use IWD\AuthCIM\Gateway\Request\Payment\RefundRequest;
use IWD\AuthCIM\Gateway\Request\Help\InitRequest;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class DeferredRefund
 * @package IWD\AuthCIM\Cron
 */
class DeferredRefund
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var Refund
     */
    private $deferredRefund;

    /**
     * @var RefundRepository
     */
    private $refundRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RefundRequest
     */
    private $refundRequest;

    /**
     * @var InitRequest
     */
    private $initRequest;

    /**
     * DeferredRefund constructor.
     *
     * @param LoggerInterface $logger
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param Refund $deferredRefund
     * @param RefundRepository $refundRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RefundRequest $refundRequest
     * @param AbstractTransaction $abstractTransaction
     * @param InitRequest $initRequest
     */
    public function __construct(
        LoggerInterface $logger,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        Refund $deferredRefund,
        RefundRepository $refundRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RefundRequest $refundRequest,
        AbstractTransaction $abstractTransaction,
        InitRequest $initRequest
    ) {
        $this->logger = $logger;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->deferredRefund = $deferredRefund;
        $this->refundRepository = $refundRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->refundRequest = $refundRequest;
        $this->abstractTransaction = $abstractTransaction;
        $this->initRequest = $initRequest;
    }

    /**
     * Try to refund deferred refunds transactions
     * @return $this
     */
    public function execute()
    {
        $success = [];
        $failed = [];

        $criteria = $this->searchCriteriaBuilder->create();
        $refunds = $this->refundRepository->getList($criteria);
        $items = $refunds->getItems();

        foreach ($items as $refund) {
            if ($this->refundTransaction($refund)) {
                $success[] = $refund->getId();
                $this->refundRepository->delete($refund);
            } else {
                $failed[] = $refund->getId();
            }
        }

        $result = !empty($success) ? 'SuccessID(' . implode(',', $success) . ');' : '';
        $result .= !empty($failed) ? 'FailedID(' . implode(',', $failed) . ');' : '';
        $result = empty($result) ? 'N/A' : $result;
        $this->logger->info('IWD Authorize.net CIM Deferred Refund: ' . $result);

        return $this;
    }

    /**
     * @param $refund Refund
     * @return bool
     */
    private function refundTransaction($refund)
    {
        /** @var $paymentInfo \Magento\Sales\Model\Order\Payment */
        $paymentInfo = $this->orderPaymentRepository->get($refund->getPaymentId());
        $payment = $this->paymentDataObjectFactory->create($paymentInfo);

        $requestInit = $this->initRequest->build(['payment' => $payment]);
        $requestRefund = $this->refundRequest->build(['payment' => $payment, 'amount' => $refund->getAmount()]);
        $request = array_merge($requestInit, $requestRefund);

        $response = $this->abstractTransaction->apiRequest($request);

        return $this->abstractTransaction->getResponseParser()->isSuccessful($response);
    }
}
