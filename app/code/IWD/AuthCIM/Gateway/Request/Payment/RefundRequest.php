<?php

namespace IWD\AuthCIM\Gateway\Request\Payment;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class RefundRequest
 * @package IWD\AuthCIM\Gateway\Request\Payment
 */
class RefundRequest extends AbstractRequest implements BuilderInterface
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
            'root' => 'createCustomerProfileTransactionRequest',
            'transaction' => [
                'profileTransRefund' => [
                    'amount' => $this->formatPrice($this->getAmount()),
                    'tax' => $this->getTax(),
                    'shipping' => $this->getShipping(),
                    'lineItems' => $this->getLineItems(),
                    'customerProfileId' => $this->getCard()->getCustomerProfileId(),
                    'customerPaymentProfileId' => $this->getCard()->getPaymentId(),
                    'customerShippingAddressId' => $this->getCustomerShippingAddressId(),
                    'creditCardNumberMasked' => $this->getCreditCardNumberMasked(),
                    'order' => [
                        'invoiceNumber' => substr($order->getOrderIncrementId(), 0, 20),
                        'description' => 'Refund order #' . $order->getOrderIncrementId(),
                    ],
                    'transId' => $this->getTransId()
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTax()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipping()
    {
        return null;
    }

    /**
     * @return string
     */
    private function getCreditCardNumberMasked()
    {
        $ccNumber = $this->getPayment()->getAdditionalInformation('cc_number');
        return 'XXXX' . substr($ccNumber, -4);
    }
}
