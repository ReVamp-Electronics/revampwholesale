<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Gateway\Http\Client\AuthorizeNetRequest;
use IWD\AuthCIM\Gateway\Response\ParseResponse;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;

/**
 * Class Method
 * @package IWD\AuthCIM\Model
 */
class AbstractTransaction
{
    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var AuthorizeNetRequest
     */
    private $authorizeNetRequest;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var ParseResponse
     */
    private $parseResponse;

    /**
     * @param TransferBuilder $transferBuilder
     * @param AuthorizeNetRequest $authorizeNetRequest
     * @param GatewayConfig $gatewayConfig
     * @param ParseResponse $parseResponse
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AuthorizeNetRequest $authorizeNetRequest,
        GatewayConfig $gatewayConfig,
        ParseResponse $parseResponse
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->authorizeNetRequest = $authorizeNetRequest;
        $this->gatewayConfig = $gatewayConfig;
        $this->parseResponse = $parseResponse;
        $this->errorMessage = '';
    }

    /**
     * @param $request
     * @return array
     */
    public function apiRequest($request)
    {
        $response = [];

        try {
            $transferBuilder = $this->getTransferBuilder($request);
            $response = $this->authorizeNetRequest->placeRequest($transferBuilder);
            if ($this->getResponseParser()->isError($response)) {
                throw new LocalizedException(__($this->getResponseParser()->getErrorMessage($response)));
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e->getMessage());
        }

        return $response;
    }

    /**
     * @param $request array
     * @return \Magento\Payment\Gateway\Http\TransferInterface
     */
    private function getTransferBuilder($request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod('POST')
            ->setUri($this->gatewayConfig->getGatewayUrl())
            ->build();
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return GatewayConfig
     */
    public function getGatewayConfig()
    {
        return $this->gatewayConfig;
    }

    /**
     * @return ParseResponse
     */
    public function getResponseParser()
    {
        return $this->parseResponse;
    }
}
