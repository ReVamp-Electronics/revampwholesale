<?php

namespace IWD\AuthCIM\Gateway\Request\Profile;

use IWD\AuthCIM\Gateway\Request\AbstractRequest;
use IWD\AuthCIM\Gateway\Request\Help\AddressRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CreateCustomerPaymentProfileRequest
 * @package IWD\AuthCIM\Gateway\Request\Profile
 */
class CreateCustomerPaymentProfileRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * @var AddressRequest
     */
    private $addressRequest;

    /**
     * ShippingRequest constructor.
     *
     * @param GatewayConfig $config
     * @param AddressRequest $addressRequest
     */
    public function __construct(
        GatewayConfig $config,
        AddressRequest $addressRequest
    ) {
        parent::__construct($config);
        $this->addressRequest = $addressRequest;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubject($buildSubject);

        $address = $this->getOrderAdapter()->getBillingAddress();

        return [
            'root' => 'createCustomerPaymentProfileRequest',
            'merchantAuthentication' => $this->getMerchantAuthentication(),
            'refId' => 'A1000127',
            'customerProfileId' => $this->getPaymentData('customer_profile'),
            'paymentProfile' => [
                'customerType' => 'individual',
                'billTo' => $this->addressRequest->getAddressArray($address),
                'payment' => $this->getPaymentInfo(),
            ],
            'validationMode' => $this->getConfig()->getValidationType()
        ];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getPaymentInfo()
    {
        if ($this->isOpaqueData()) {
            $paymentData = $this->getOpaqueData();
        } elseif ($this->isBankAccount()) {
            $paymentData = $this->getBankAccountData();
        } elseif ($this->isCreditCard()) {
            $paymentData = $this->getCreditCardData();
        } else {
            throw new LocalizedException(__('Incorrect payment data type'));
        }

        return $paymentData;
    }

    /**
     * @return bool
     */
    private function isCreditCard()
    {
        return $this->getPaymentData('cc_number') != null;
    }

    /**
     * @return bool
     */
    private function isOpaqueData()
    {
        return $this->getPaymentData('opaque_descriptor') != null
            && $this->getPaymentData('opaque_value') != null;
    }

    /**
     * @return bool
     */
    private function isBankAccount()
    {
        return $this->getPaymentData('routing_number') != null
            && $this->getPaymentData('account_number') != null;
    }

    /**
     * Credit Card. Contains credit card payment information for the customer profile.
     *
     * @return array
     */
    private function getCreditCardData()
    {
        return [
            'creditCard' => [
                'cardNumber' => $this->getPaymentData('cc_number'),
                'expirationDate' => $this->getExpirationDate(),
                'cardCode' => $this->getPaymentData('cc_cid')
            ]
        ];
    }

    /**
     * Accept.js. Use this field and its children to pass an Accept payment nonce instead of credit card information.
     *
     * @return array
     */
    private function getOpaqueData()
    {
        $dataDescriptor = $this->getPaymentData('opaque_descriptor');
        $dataValue = $this->getPaymentData('opaque_value');

        return [
            'opaqueData' => [
                'dataDescriptor' => $dataDescriptor,
                'dataValue' => $dataValue
            ]
        ];
    }

    /**
     * @param $key
     * @return null
     */
    private function getPaymentData($key)
    {
        $payment = $this->getPayment();

        if ($payment->hasData($key) && $payment->getData($key)) {
            return $payment->getData($key);
        } elseif ($payment->hasAdditionalInformation($key) && $payment->getAdditionalInformation($key)) {
            return $payment->getAdditionalInformation($key);
        } else {
            return null;
        }
    }

    /**
     * eCheck. Contains bank account payment information for the customer profile.
     *
     * @return array
     */
    private function getBankAccountData()
    {
        return [
            'bankAccount' => [
                'accountType' => $this->getPaymentData('account_type'),
                'routingNumber' => $this->getPaymentData('routing_number'),
                'accountNumber' => $this->getPaymentData('account_number'),
                'nameOnAccount' => $this->getPaymentData('name_on_account'),
                'echeckType' => $this->getPaymentData('echeck_type'),
                'bankName' => $this->getPaymentData('bank_name')
            ]
        ];
    }

    /**
     * @return string
     */
    private function getExpirationDate()
    {
        return sprintf(
            "%02d%02d",
            $this->getPaymentData('cc_exp_month'),
            substr($this->getPaymentData('cc_exp_year'), -2)
        );
    }
}
