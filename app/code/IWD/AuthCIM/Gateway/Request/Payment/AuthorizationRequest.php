<?php

namespace IWD\AuthCIM\Gateway\Request\Payment;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AuthorizationRequest
 * @package IWD\AuthCIM\Gateway\Request\Payment
 */
class AuthorizationRequest extends AbstractRequest implements BuilderInterface
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

        return [
            'root' => 'createTransactionRequest',
            'transactionRequest' => [
                'transactionType' => 'authOnlyTransaction',
                'amount' => $this->formatPrice($this->getAmount()),
                'profile' => [
                    'customerProfileId' => $this->getCard()->getCustomerProfileId(),
                    'paymentProfile' => [
                        'paymentProfileId' => $this->getCard()->getPaymentId()
                    ]
                ],
                'order' => [
                    'invoiceNumber' => substr($order->getOrderIncrementId(), 0, 20),
                    'description' => substr('Authorize order #' . $order->getOrderIncrementId(), 0, 255),
                ],
                'lineItems' => $this->getLineItems(),
                'transactionSettings' => $this->getTransactionSettings(),
            ]
        ];
    }
}
