<?php

namespace IWD\AuthCIM\Gateway\Request\Profile;

use IWD\AuthCIM\Gateway\Request\AbstractRequest;
use IWD\AuthCIM\Gateway\Request\Help\AddressRequest;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CreateCustomerShippingAddressRequest
 * @package IWD\AuthCIM\Gateway\Request\Help
 */
class CreateCustomerShippingAddressRequest extends AbstractRequest implements BuilderInterface
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
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubject($buildSubject);

        if (!isset($buildSubject['customerProfileId'])) {
            throw new LocalizedException(__('CustomerProfileId should be set before create shipping address'));
        }

        $order = $this->getOrderAdapter();
        $address = $order->getShippingAddress();
        $address = empty($address) ? $order->getBillingAddress() : $address;

        return [
            'root' => 'createCustomerShippingAddressRequest',
            'merchantAuthentication' => [
                'name' => $this->getConfig()->getApiLoginId(),
                'transactionKey' => $this->getConfig()->getTransKey()
            ],
            'customerProfileId' => $buildSubject['customerProfileId'],
            'address' => $this->addressRequest->getAddressArray($address)
        ];
    }
}
