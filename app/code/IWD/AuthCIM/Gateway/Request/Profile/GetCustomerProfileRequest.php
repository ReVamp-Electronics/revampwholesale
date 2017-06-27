<?php

namespace IWD\AuthCIM\Gateway\Request\Profile;

use IWD\AuthCIM\Gateway\Request\AbstractRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class GetCustomerProfileRequest
 * @package IWD\AuthCIM\Gateway\Request\Profile
 */
class GetCustomerProfileRequest extends AbstractRequest implements BuilderInterface
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

        return [
            'root' => 'getCustomerProfileRequest',
            'merchantAuthentication' => $this->getMerchantAuthentication(),
            'customerProfileId' => $payment['customerProfileId'],
        ];
    }
}
