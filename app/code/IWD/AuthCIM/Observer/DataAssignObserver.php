<?php

namespace IWD\AuthCIM\Observer;

use IWD\AuthCIM\Model\Ui\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class DataAssignObserver
 * @package IWD\AuthCIM\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var \IWD\AuthCIM\Model\Payment\Info
     */
    private $paymentInfo;

    /**
     * DataAssignObserver constructor.
     * @param \IWD\AuthCIM\Model\Payment\Info $paymentInfo
     */
    public function __construct(\IWD\AuthCIM\Model\Payment\Info $paymentInfo)
    {
        $this->paymentInfo = $paymentInfo;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $payment = $this->readPaymentModelArgument($observer);
        if ($payment->getMethod() != ConfigProvider::CODE) {
            return;
        }

        $dataArgument = $this->readDataArgument($observer);
        $additionalData = $dataArgument->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            $additionalData = [];
        }

        $data = $dataArgument->getData();
        if (!is_array($additionalData)) {
            $data = [];
        }

        $data = array_merge($additionalData, $data);
        if (empty($data)) {
            return;
        }

        $this->paymentInfo->preparePaymentInfo($payment, $data);
    }
}
