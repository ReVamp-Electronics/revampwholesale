<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Gateway\Request\Help\AuthenticationRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Gateway\Http\Client\AuthorizeNetRequest;
use IWD\AuthCIM\Gateway\Response\ParseResponse;
use Magento\Payment\Gateway\Http\TransferBuilder;

/**
 * Class Method
 * @package IWD\AuthCIM\Model
 */
class Method extends AbstractTransaction
{
    /**
     * @var AuthenticationRequest
     */
    private $authenticationRequest;

    /**
     * @var Refund
     */
    private $deferredRefund;

    /**
     * @var RefundRepository
     */
    private $refundRepository;

    /**
     * Method constructor.
     *
     * @param TransferBuilder $transferBuilder
     * @param AuthorizeNetRequest $authorizeNetRequest
     * @param GatewayConfig $gatewayConfig
     * @param ParseResponse $parseResponse
     * @param AuthenticationRequest $authenticationRequest
     * @param Refund $deferredRefund
     * @param RefundRepository $refundRepository
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AuthorizeNetRequest $authorizeNetRequest,
        GatewayConfig $gatewayConfig,
        ParseResponse $parseResponse,
        AuthenticationRequest $authenticationRequest,
        Refund $deferredRefund,
        RefundRepository $refundRepository
    ) {
        parent::__construct(
            $transferBuilder,
            $authorizeNetRequest,
            $gatewayConfig,
            $parseResponse
        );

        $this->authenticationRequest = $authenticationRequest;
        $this->deferredRefund = $deferredRefund;
        $this->refundRepository = $refundRepository;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getGatewayConfig()->isActive();
    }

    /**
     * @return bool
     */
    public function checkApiCredentials()
    {
        try {
            $request = $this->authenticationRequest->build([]);
            $response = $this->apiRequest($request);

            return $this->getResponseParser()->isSuccessful($response);
        } catch (\Exception $e) {
            $this->setErrorMessage($e->getMessage());
            return false;
        }
    }

    /**
     * @param $paymentId
     * @param $amount
     */
    public function saveRefundForTryingLate($paymentId, $amount)
    {
        $this->deferredRefund
            ->setAmount($amount)
            ->setPaymentId($paymentId);

        $this->refundRepository->save($this->deferredRefund);
    }
}
