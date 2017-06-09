<?php

namespace IWD\OrderManager\Model\Order;

use IWD\OrderManager\Model\Log\Logger;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use IWD\OrderManager\Model\Quote\Quote as OrderManagerQuote;

/**
 * Class Payment
 * @package IWD\OrderManager\Model\Order
 */
class Payment extends AbstractModel
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * @var []
     */
    private $paymentData;

    /**
     * @var OrderManagerQuote
     */
    private $quote;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    private $methodSpecificationFactory;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

    /**
     * @var DataObject
     */
    private $paymentDataObject;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @param OrderManagerQuote $quote
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\OrderManager\Model\Order\Order $order,
        OrderManagerQuote $quote,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        DataObject $paymentDataObject,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->order = $order;
        $this->quote = $quote;
        $this->paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->paymentDataObject = $paymentDataObject;
        $this->eventManager = $context->getEventDispatcher();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param [] $paymentData
     * @return $this
     */
    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @return DataObject
     */
    public function getPaymentDataArray()
    {
        return $this->paymentDataObject->addData($this->getPaymentData());
    }

    /**
     * @return Order
     */
    protected function loadOrder()
    {
        $id = $this->getOrderId();
        return $this->order->load($id);
    }

    /**
     * @return void
     */
    public function editPaymentMethod()
    {
        $order = $this->loadOrder();
        $paymentData = $this->getPaymentData();
        $paymentDataArray = $this->getPaymentDataArray();

        $oldPaymentTitle = $order->getPayment()->getMethodInstance()->getTitle();

        $payment = $order->getPayment();

        $this->refundPreviousTransactions($payment);

        $payment->addData($paymentData);
        $payment->unsetData('method_instance');
        $payment->save();

        $this->paymentMethodAssignDataEvent($payment);

        $payment->getMethodInstance()->assignData($paymentDataArray);
        $payment->save();
        $payment->getOrder()->save();

        $order->place();
        $order->getPayment()->save();
        $order->getPayment()->getOrder()->save();

        $this->updateQuotePayment();

        $newPaymentTitle = $order->getPayment()->getMethodInstance()->getTitle();
        Logger::getInstance()->addChange('Payment method', $oldPaymentTitle, $newPaymentTitle);
    }
    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     */
    private function paymentMethodAssignDataEvent($payment)
    {
        $data = clone $this->paymentDataObject;
        $data->setData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA, $payment->getData());

        $this->eventManager->dispatch(
            'payment_method_assign_data_' . $payment->getMethod(),
            [
                AbstractDataAssignObserver::METHOD_CODE => $payment,
                AbstractDataAssignObserver::MODEL_CODE => $payment->getMethodInstance()->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        $this->eventManager->dispatch(
            'payment_method_assign_data',
            [
                AbstractDataAssignObserver::METHOD_CODE => $payment,
                AbstractDataAssignObserver::MODEL_CODE => $payment->getMethodInstance()->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        return $this;
    }

    /**
     * @param $payment
     * @return bool
     */
    private function refundPreviousTransactions($payment)
    {
        if ($payment->getMethod() == 'iwd_authcim') {
            try {
                $payment->getMethodInstance()->void($payment);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return void
     */
    private function updateQuotePayment()
    {
        $quoteId = $this->loadOrder()->getQuoteId();
        $paymentDataArray = $this->getPaymentDataArray();
        $quote = $this->quote->load($quoteId);
        $quote->getPayment()->setMethod($paymentDataArray['method']);
        $this->quote->save($quote);
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        $order = $this->loadOrder();
        $quoteId = $order->getQuoteId();
        $quote = $this->quote->load($quoteId);
        $store = $quote ? $quote->getStoreId() : null;
        $currentMethod = $order->getPayment()->getMethod();
        $specification = $this->methodSpecificationFactory->create([AbstractMethod::CHECK_ZERO_TOTAL]);

        /* hack: related with update shipping amount for order with custom price */
        $quote->setBaseGrandTotal(
            $quote->getBaseGrandTotal()
            - $quote->getShippingAddress()->getBaseShippingAmount()
            + $order->getBaseShippingAmount()
        )->setGrandTotal(
            $quote->getGrandTotal()
            - $quote->getShippingAddress()->getShippingAmount()
            + $order->getShippingAmount()
        )->save();

        $storeMethods = $this->paymentHelper->getStoreMethods($store, $quote);
        foreach ($storeMethods as $method) {
            if ($this->canUseMethod($method, $quote)
                && $specification->isApplicable($method, $quote)
                && $currentMethod == $method->getCode()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Payment\Model\MethodInterface $method
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    private function canUseMethod($method, $quote)
    {
        return $this->methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        )->isApplicable($method, $quote);
    }
}
