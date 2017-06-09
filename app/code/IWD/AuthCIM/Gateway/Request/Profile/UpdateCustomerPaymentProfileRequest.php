<?php

namespace IWD\AuthCIM\Gateway\Request\Profile;

/**
 * Class UpdateCustomerPaymentProfileRequest
 * @package IWD\AuthCIM\Gateway\Request\Profile
 */
class UpdateCustomerPaymentProfileRequest extends CreateCustomerPaymentProfileRequest
{
    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $request = parent::build($buildSubject);
        $payment = $this->getPayment();

        $request['root'] = 'updateCustomerPaymentProfileRequest';
        $request['paymentProfile']['customerPaymentProfileId'] = $payment->getAdditionalInformation('payment_profile');

        return $request;
    }
}
