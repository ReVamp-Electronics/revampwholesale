<?php

namespace IWD\AuthCIM\Gateway\Request;

use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class AbstractRequest
 * @package IWD\AuthCIM\Gateway\Request
 */
class AbstractRequest
{
    use Formatter;

    /**
     * @var array
     */
    private $buildSubject;

    /**
     * @var GatewayConfig
     */
    private $config;

    /**
     * @param GatewayConfig $config
     */
    public function __construct(
        GatewayConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @param $buildSubject
     */
    public function setBuildSubject($buildSubject)
    {
        if (!isset($buildSubject['payment'])) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $this->buildSubject = $buildSubject;
    }

    /**
     * @return array
     */
    public function getBuildSubject()
    {
        return $this->buildSubject;
    }

    /**
     * @return null|string
     */
    public function getCustomerIP()
    {
        $ip = $this->getOrderAdapter()->getRemoteIp();
        return empty($ip) ? null : $ip;
    }

    /**
     * @return array
     */
    public function getTransactionSettings()
    {
        return [
            'setting' => [
                'settingName' => 'duplicateWindow',
                'settingValue' => '0'
            ]
        ];
    }

    /**
     * @return array|null
     */
    public function getShipping()
    {
        $shipping = $this->getOrderAdapter()->getShippingAmount();
        if (!empty($shipping)) {
            return [
                'amount' => $this->formatPrice($shipping),
                'name' => substr($this->getOrderAdapter()->getShippingDescription(), 0, 31)
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getTax()
    {
        $tax = $this->getOrderAdapter()->getTaxAmount();
        if (!empty($tax)) {
            return [
                'amount' => $this->formatPrice($tax),
                'name' => 'Tax Amount'
            ];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getLineItems()
    {
        if (!$this->config->getSendLineItems()) {
            return null;
        }

        $lineItems = [];

        /** @var $orderItems \Magento\Sales\Api\Data\OrderItemInterface[] */
        $orderItems = $this->getOrderAdapter()->getItems();
        foreach ($orderItems as $orderItem) {
            $lineItems[] = [
                'lineItem' => [
                    'itemId' => substr($orderItem->getSku(), 0, 31),
                    'name' => substr($orderItem->getName(), 0, 31),
                    'description' => substr($orderItem->getDescription(), 0, 31),
                    'quantity' => $orderItem->getQtyOrdered(),
                    'unitPrice' => $orderItem->getPrice(),
                    'taxable' => ($orderItem->getTaxAmount() > 0) ? 'true' : 'false'
                ]
            ];
        }

        return $lineItems;
    }

    /**
     * @return GatewayConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \Magento\Payment\Model\InfoInterface
     */
    public function getPayment()
    {
        $payment = $this->buildSubject['payment'];
        return $payment->getPayment();
    }

    /**
     * @return \Magento\Payment\Gateway\Data\OrderAdapterInterface|\IWD\AuthCIM\Gateway\Data\Order\OrderAdapter
     */
    public function getOrderAdapter()
    {
        $payment = $this->buildSubject['payment'];
        return $payment->getOrder();
    }

    /**
     * Reads amount from subject
     *
     * @return mixed
     */
    public function getAmount()
    {
        if (!isset($this->buildSubject['amount']) || !is_numeric($this->buildSubject['amount'])) {
            throw new \InvalidArgumentException('Amount should be provided');
        }

        return $this->buildSubject['amount'];
    }

    /**
     * @return bool
     */
    public function hasTransactionId()
    {
        $transactionId = $this->getTransId();
        return !empty($transactionId);
    }

    /**
     * @return string
     */
    public function getTransId()
    {
        return $this->getPayment()->getTransId();
    }

    /**
     * @return array
     */
    public function getMerchantAuthentication()
    {
        return [
            'name' => $this->getConfig()->getApiLoginId(),
            'transactionKey' => $this->getConfig()->getTransKey()
        ];
    }
}
