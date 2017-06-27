<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Gateway\Request\Profile\CreateCustomerPaymentProfileRequest;
use IWD\AuthCIM\Gateway\Request\Profile\DeleteCustomerPaymentProfileRequest;
use IWD\AuthCIM\Gateway\Request\Profile\UpdateCustomerPaymentProfileRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Gateway\Http\Client\AuthorizeNetRequest;
use IWD\AuthCIM\Gateway\Response\ParseResponse;
use IWD\AuthCIM\Gateway\Data\Order\AddressAdapter;
use IWD\AuthCIM\Gateway\Data\PaymentDataObjectFactory;
use IWD\AuthCIM\Gateway\Data\Order\OrderAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CustomerProfile
 * @package IWD\AuthCIM\Model
 */
class PaymentProfile extends AbstractTransaction
{
    /**
     * @var CreateCustomerPaymentProfileRequest
     */
    private $createPaymentProfileRequest;

    /**
     * @var DeleteCustomerPaymentProfileRequest
     */
    private $deletePaymentProfileRequest;

    /**
     * @var UpdateCustomerPaymentProfileRequest
     */
    private $updatePaymentProfileRequest;

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
     * @var \IWD\AuthCIM\Model\Payment\Info
     */
    private $paymentInfo;

    /**
     * PaymentProfile constructor.
     * @param TransferBuilder $transferBuilder
     * @param AuthorizeNetRequest $authorizeNetRequest
     * @param GatewayConfig $gatewayConfig
     * @param ParseResponse $parseResponse
     * @param CreateCustomerPaymentProfileRequest $createPaymentProfileRequest
     * @param DeleteCustomerPaymentProfileRequest $deletePaymentProfileRequest
     * @param UpdateCustomerPaymentProfileRequest $updatePaymentProfileRequest
     * @param Payment $paymentData
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param OrderAdapter $orderAdapter
     * @param AddressAdapter $addressAdapter
     * @param Payment\Info $paymentInfo
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AuthorizeNetRequest $authorizeNetRequest,
        GatewayConfig $gatewayConfig,
        ParseResponse $parseResponse,
        CreateCustomerPaymentProfileRequest $createPaymentProfileRequest,
        DeleteCustomerPaymentProfileRequest $deletePaymentProfileRequest,
        UpdateCustomerPaymentProfileRequest $updatePaymentProfileRequest,
        Payment $paymentData,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        OrderAdapter $orderAdapter,
        AddressAdapter $addressAdapter,
        \IWD\AuthCIM\Model\Payment\Info $paymentInfo
    ) {
        parent::__construct(
            $transferBuilder,
            $authorizeNetRequest,
            $gatewayConfig,
            $parseResponse
        );

        $this->createPaymentProfileRequest = $createPaymentProfileRequest;
        $this->deletePaymentProfileRequest = $deletePaymentProfileRequest;
        $this->updatePaymentProfileRequest = $updatePaymentProfileRequest;

        $this->paymentData = $paymentData;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->orderAdapter = $orderAdapter;

        $this->addressAdapter = $addressAdapter;
        $this->paymentInfo = $paymentInfo;
    }

    /**
     * @param $buildSubject
     * @return string|null
     */
    public function createPaymentProfile($buildSubject)
    {
        $request = $this->createPaymentProfileRequest->build($buildSubject);
        $response = $this->apiRequest($request);

        return $this->parseCreatePaymentProfileResponse($response);
    }

    /**
     * @param $response
     * @return string|null
     * @throws LocalizedException
     */
    private function parseCreatePaymentProfileResponse($response)
    {
        $profileId = isset($response["customerPaymentProfileId"]) ? $response["customerPaymentProfileId"] : null;

        if ($profileId == null) {
            throw new LocalizedException(__('Can not create customer payment profile'));
        }

        return $profileId;
    }

    /**
     * @param $customerProfileId
     * @param $paymentProfileId
     * @return string|null
     */
    public function deletePaymentProfile($customerProfileId, $paymentProfileId)
    {
        $buildSubject = [
            'payment' => [
                'paymentProfileId' => $paymentProfileId,
                'customerProfileId' => $customerProfileId
            ]
        ];

        $request = $this->deletePaymentProfileRequest->build($buildSubject);
        $response = $this->apiRequest($request);

        return $this->parseDeletePaymentProfileResponse($response);
    }

    /**
     * @param $response
     * @return string|null
     * @throws LocalizedException
     */
    private function parseDeletePaymentProfileResponse($response)
    {
        $parser = $this->getResponseParser();

        if ($this->isRecordCannotBeFound($response)) {
            return true;
        }

        if ($parser->isError($response)) {
            $error = $parser->getErrorMessage($response);
            throw new LocalizedException(__($error));
        }

        return $parser->isSuccessful($response);
    }

    /**
     * @param $response
     * @return bool
     */
    private function isRecordCannotBeFound($response)
    {
        return $this->getResponseParser()->getErrorCode($response) == 'E00040';
    }

    /**
     * @param $buildSubject
     * @return string|null
     */
    public function updatePaymentProfile($buildSubject)
    {
        $request = $this->updatePaymentProfileRequest->build($buildSubject);
        $response = $this->apiRequest($request);

        return $this->parseUpdatePaymentProfileResponse($response);
    }

    /**
     * @param $customerProfileId
     * @param $paymentProfileId
     * @param $address
     * @param $payment
     * @return null|string
     */
    public function prepareBuildSubject($customerProfileId, $paymentProfileId, $address, $payment)
    {
        $this->paymentInfo->preparePaymentInfo($this->paymentData, $payment);
        $this->paymentData //->setData($payment)
            ->setAdditionalInformation('customer_profile', $customerProfileId)
            ->setAdditionalInformation('payment_profile', $paymentProfileId);

        $billingAddress = $this->addressAdapter->setData($address);
        $this->orderAdapter->setBillingAddress($billingAddress);

        return [
            'payment' => $this->paymentDataObjectFactory->create($this->paymentData, $this->orderAdapter)
        ];
    }

    /**
     * @param $response
     * @return string|null
     * @throws LocalizedException
     */
    private function parseUpdatePaymentProfileResponse($response)
    {
        $parser = $this->getResponseParser();

        if ($parser->isError($response)) {
            $error = $parser->getErrorMessage($response);
            throw new LocalizedException(__($error));
        }

        return $parser->parseResponse($response);
    }
}
