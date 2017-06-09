<?php

namespace IWD\AuthCIM\Gateway\Request\Profile;

use IWD\AuthCIM\Gateway\Request\AbstractRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class DeleteCustomerPaymentProfileRequest
 * @package IWD\AuthCIM\Gateway\Request\Profile
 */
class DeleteCustomerPaymentProfileRequest extends AbstractRequest implements BuilderInterface
{
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
        $payment = $buildSubject['payment'];

        if (!isset($payment['customerProfileId']) || empty($payment['customerProfileId'])) {
            throw new LocalizedException(__('CustomerProfileId is empty'));
        }

        if (!isset($payment['paymentProfileId']) || empty($payment['paymentProfileId'])) {
            throw new LocalizedException(__('PaymentProfileId is empty'));
        }

        return [
            'root' => 'deleteCustomerPaymentProfileRequest',
            'merchantAuthentication' => $this->getMerchantAuthentication(),
            'customerProfileId' => $payment['customerProfileId'],
            'customerPaymentProfileId' => $payment['paymentProfileId']
        ];
    }
}
