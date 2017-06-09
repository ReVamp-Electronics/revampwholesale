<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Gateway\Request\Payment\VoidRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Gateway\Http\Client\AuthorizeNetRequest;
use IWD\AuthCIM\Gateway\Response\ParseResponse;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;

/**
 * Class VoidTransaction
 * @package IWD\AuthCIM\Model
 */
class VoidTransaction extends AbstractTransaction
{
    /**
     * @var VoidRequest
     */
    private $voidRequest;

    /**
     * @param TransferBuilder $transferBuilder
     * @param AuthorizeNetRequest $authorizeNetRequest
     * @param GatewayConfig $gatewayConfig
     * @param ParseResponse $parseResponse
     * @param VoidRequest $voidRequest
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AuthorizeNetRequest $authorizeNetRequest,
        GatewayConfig $gatewayConfig,
        ParseResponse $parseResponse,
        VoidRequest $voidRequest
    ) {
        parent::__construct(
            $transferBuilder,
            $authorizeNetRequest,
            $gatewayConfig,
            $parseResponse
        );

        $this->voidRequest = $voidRequest;
    }

    /**
     * @param $payment
     * @param $order
     * @return string|null
     */
    public function voidTransaction($payment, $order)
    {
        $request = $this->voidRequest->build(['payment' => $payment, 'order' => $order]);
        $response = $this->apiRequest($request);

        return $this->parseVoidTransactionResponse($response);
    }

    /**
     * @param $response
     * @throws LocalizedException
     */
    private function parseVoidTransactionResponse($response)
    {
        if ($this->getResponseParser()->isError($response)) {
            $message = $this->getResponseParser()->getErrorMessage($response);
            throw new LocalizedException(__($message));
        }
    }
}
