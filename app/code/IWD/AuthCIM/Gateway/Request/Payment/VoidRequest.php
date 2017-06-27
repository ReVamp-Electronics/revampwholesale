<?php

namespace IWD\AuthCIM\Gateway\Request\Payment;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class VoidRequest
 * @package IWD\AuthCIM\Gateway\Request\Payment
 */
class VoidRequest extends AbstractRequest implements BuilderInterface
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

        return [
            'root' => 'createCustomerProfileTransactionRequest',
            'transaction' => [
                'profileTransVoid' => [
                    'customerProfileId' => $this->getCard()->getCustomerProfileId(),
                    'customerPaymentProfileId' => $this->getCard()->getPaymentId(),
                    'customerShippingAddressId' => $this->getCustomerShippingAddressId(),
                    'transId' => $this->getTransId()
                ]
            ]
        ];
    }
}
