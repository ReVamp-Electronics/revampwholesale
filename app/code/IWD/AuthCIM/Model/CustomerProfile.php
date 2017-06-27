<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Gateway\Request\Profile\CreateCustomerProfileRequest;
use IWD\AuthCIM\Gateway\Request\Profile\GetCustomerProfileRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Gateway\Http\Client\AuthorizeNetRequest;
use IWD\AuthCIM\Gateway\Response\ParseResponse;
use IWD\AuthCIM\Gateway\Data\PaymentDataObjectFactory;
use IWD\AuthCIM\Gateway\Data\Order\OrderAdapter;
use IWD\AuthCIM\Gateway\Data\Order\AddressAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CustomerProfile
 * @package IWD\AuthCIM\Model
 */
class CustomerProfile extends AbstractTransaction
{
    /**
     * @var CreateCustomerProfileRequest
     */
    private $createCustomerProfileRequest;

    /**
     * @var Payment
     */
    private $paymentData;

    /**
     * @var OrderAdapter
     */
    private $orderAdapter;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var AddressAdapter
     */
    private $addressAdapter;

    /**
     * @var GetCustomerProfileRequest
     */
    private $getCustomerProfileRequest;

    /**
     * CustomerProfile constructor.
     * @param TransferBuilder $transferBuilder
     * @param AuthorizeNetRequest $authorizeNetRequest
     * @param GatewayConfig $gatewayConfig
     * @param ParseResponse $parseResponse
     * @param CreateCustomerProfileRequest $createCustomerProfileRequest
     * @param GetCustomerProfileRequest $getCustomerProfileRequest
     * @param Payment $paymentData
     * @param OrderAdapter $orderAdapter
     * @param AddressAdapter $addressAdapter
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AuthorizeNetRequest $authorizeNetRequest,
        GatewayConfig $gatewayConfig,
        ParseResponse $parseResponse,
        CreateCustomerProfileRequest $createCustomerProfileRequest,
        GetCustomerProfileRequest $getCustomerProfileRequest,
        Payment $paymentData,
        OrderAdapter $orderAdapter,
        AddressAdapter $addressAdapter,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        parent::__construct(
            $transferBuilder,
            $authorizeNetRequest,
            $gatewayConfig,
            $parseResponse
        );

        $this->createCustomerProfileRequest = $createCustomerProfileRequest;
        $this->getCustomerProfileRequest = $getCustomerProfileRequest;
        $this->paymentData = $paymentData;
        $this->orderAdapter = $orderAdapter;
        $this->addressAdapter = $addressAdapter;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * @param $buildSubject
     * @return string|null
     */
    public function createCustomerProfile($buildSubject)
    {
        $request = $this->createCustomerProfileRequest->build($buildSubject);
        $response = $this->apiRequest($request);

        return $this->parseCreateCustomerProfileResponse($response);
    }

    /**
     * @param $response
     * @return null
     * @throws LocalizedException
     */
    private function parseCreateCustomerProfileResponse($response)
    {
        $customerProfileId = null;

        if ($this->getResponseParser()->isError($response)) {
            /** E00039 - Profile is already exists */
            if ($this->getResponseParser()->getErrorCode($response) == 'E00039') {
                preg_match('!\d+!', $response['messages']['message']['text'], $matches);
                $customerProfileId = isset($matches[0]) ? $matches[0] : null;
            }
        } else {
            $customerProfileId = isset($response["customerProfileId"]) ? $response["customerProfileId"] : null;
        }

        if ($customerProfileId == null) {
            throw new LocalizedException(__('Can not create customer profile'));
        }

        return $customerProfileId;
    }

    /**
     * @param $customerProfileId
     * @return array|null
     */
    public function getCustomerProfileRequest($customerProfileId)
    {
        $buildSubject = [
            'payment' => ['customerProfileId' => $customerProfileId]
        ];

        $request = $this->getCustomerProfileRequest->build($buildSubject);
        $response = $this->apiRequest($request);

        return $this->parseGetCustomerProfileRequest($response);
    }

    /**
     * @param $response
     * @return array|null
     * @throws LocalizedException
     */
    private function parseGetCustomerProfileRequest($response)
    {
        if ($this->getResponseParser()->isError($response)) {
            $code = $this->getResponseParser()->getErrorCode($response);

            if ($code == 'E00040') {
                //The record cannot be found
                return null;
            } elseif ($code == 'E00003') {
                throw new LocalizedException(__('The customer profile ID is invalid according to its data type'));
            } elseif ($code == 'E00013') {
                throw new LocalizedException(__('The customer profile ID is invalid'));
            } else {
                throw new LocalizedException(__($this->getResponseParser()->getErrorMessage($response)));
            }
        }

        if (!isset($response['profile'])) {
            throw new LocalizedException(__('The customer profile does not exists'));
        }

        return $response['profile'];
    }

    /**
     * @param $customerId
     * @param $address
     * @return array
     */
    public function prepareBuildSubject($customerId, $address)
    {
        $this->addressAdapter->setData($address);
        $this->orderAdapter->setBillingAddress($this->addressAdapter);
        $this->orderAdapter->setCustomerId($customerId);

        return [
            "payment" => $this->paymentDataObjectFactory->create($this->paymentData, $this->orderAdapter)
        ];
    }
}
