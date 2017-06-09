<?php

namespace IWD\AuthCIM\Gateway\Request\Profile;

use IWD\AuthCIM\Gateway\Request\AbstractRequest;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CreateCustomerProfileRequest
 * @package IWD\AuthCIM\Gateway\Request\Profile
 */
class CreateCustomerProfileRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubject($buildSubject);

        $order = $this->getOrderAdapter();
        $address = $order->getBillingAddress();

        $description = $order->getCustomerId()
            ? 'Profile for customer ID ' . $order->getCustomerId()
            : 'Profile for guest customer';

        return [
            'root' => 'createCustomerProfileRequest',
            'merchantAuthentication' => $this->getMerchantAuthentication(),
            'refId' => 'A1000127',
            'profile' => [
                'merchantCustomerId' => $order->getCustomerId(),
                'description' => $description,
                'email' => $address->getEmail()
            ],
        ];
    }
}
