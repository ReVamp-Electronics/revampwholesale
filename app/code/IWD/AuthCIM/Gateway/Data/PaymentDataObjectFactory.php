<?php

namespace IWD\AuthCIM\Gateway\Data;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class PaymentDataObjectFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates Payment Data Object
     *
     * @param $payment
     * @param $order
     * @return PaymentDataObjectInterface
     */
    public function create($payment, $order)
    {
        $data = [
            'order' => $order,
            'payment' => $payment
        ];

        return $this->objectManager->create(
            'Magento\Payment\Gateway\Data\PaymentDataObject',
            $data
        );
    }
}
