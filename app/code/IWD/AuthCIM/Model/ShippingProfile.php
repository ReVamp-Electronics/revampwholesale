<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Gateway\Request\Profile\CreateCustomerShippingAddressRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Gateway\Http\Client\AuthorizeNetRequest;
use IWD\AuthCIM\Gateway\Response\ParseResponse;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;

/**
 * Class ShippingProfile
 * @package IWD\AuthCIM\Model
 */
class ShippingProfile extends AbstractTransaction
{
    /**
     * @var CreateCustomerShippingAddressRequest
     */
    private $createCustomerShippingAddressRequest;

    /**
     * @param TransferBuilder $transferBuilder
     * @param AuthorizeNetRequest $authorizeNetRequest
     * @param GatewayConfig $gatewayConfig
     * @param ParseResponse $parseResponse
     * @param CreateCustomerShippingAddressRequest $createCustomerShippingAddressRequest
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AuthorizeNetRequest $authorizeNetRequest,
        GatewayConfig $gatewayConfig,
        ParseResponse $parseResponse,
        CreateCustomerShippingAddressRequest $createCustomerShippingAddressRequest
    ) {
        parent::__construct(
            $transferBuilder,
            $authorizeNetRequest,
            $gatewayConfig,
            $parseResponse
        );

        $this->createCustomerShippingAddressRequest = $createCustomerShippingAddressRequest;
    }

    /**
     * @param $buildSubject
     * @return string|null
     */
    public function createShippingAddress($buildSubject)
    {
        $request = $this->createCustomerShippingAddressRequest->build($buildSubject);
        $response = $this->apiRequest($request);

        return $this->parseCreateShippingAddressResponse($response);
    }

    /**
     * @param $response
     * @return null
     * @throws LocalizedException
     */
    private function parseCreateShippingAddressResponse($response)
    {
        $profileId = isset($response["customerAddressId"]) ? $response["customerAddressId"] : null;

        if ($profileId == null) {
            throw new LocalizedException(__('Can not create customer address profile'));
        }

        return $profileId;
    }
}
