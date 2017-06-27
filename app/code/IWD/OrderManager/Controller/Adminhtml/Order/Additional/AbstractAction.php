<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Additional;

use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Model\Quote\Quote;
use IWD\OrderManager\Model\Order\Shipping;
use IWD\OrderManager\Model\Order\Payment;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractAction
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Additional
 */
abstract class AbstractAction extends \IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Shipping
     */
    protected $shipping;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool
     */
    private $formAfterReAuthorization =  false;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param Quote $quote
     * @param Order $order
     * @param Shipping $shipping
     * @param Payment $payment
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        Quote $quote,
        Order $order,
        Shipping $shipping,
        Payment $payment
    ) {
        parent::__construct($context, $resultPageFactory, $helper, AbstractAction::ACTION_UPDATE);
        $this->quote = $quote;
        $this->order = $order;
        $this->shipping = $shipping;
        $this->payment = $payment;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return Shipping
     */
    protected function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @return Payment
     */
    protected function getPayment()
    {
        $this->payment->setOrderId($this->getOrder()->getId());
        return $this->payment;
    }

    /**
     * @return Quote
     * @throws \Exception
     */
    protected function getQuote()
    {
        return $this->quote;
    }

    /**
     * @return Quote
     * @throws \Exception
     */
    protected function loadQuote()
    {
        $quoteId = $this->getOrder()->getQuoteId();
        $this->quote->load($quoteId);
        return $this->quote;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function loadOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $this->order->load($id);
        if (!$this->order->getEntityId()) {
            throw new LocalizedException(__('Can not load order'));
        }
        return $this->order;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    protected function getResultHtml()
    {
        // step 0: update edited information (ordered items, order address ...)
        if (!$this->getRequest()->getParam('skip_save', false)) {
            $this->update();
        }

        $this->prepareObjects();

        // step 1: update shipping method
        if ($this->needUpdateShippingInfo()) {
            if ($this->isUpdateShippingInfoAutomatically()) {
                $this->updateShippingInfo();
            } else {
                return $this->formForUpdateShippingInfo();
            }
        }

        // step 2: update payment method
        $this->getOrder()->syncQuote();
        if ($this->needUpdatePaymentInfo()) {
            return $this->formForUpdatePaymentInfo();
        }

        // step 3: re-authorize new order amount
        $this->doReAuthorization();

        // step 4: additional actions: multi stock, ...
        $this->_eventManager->dispatch('iwd_ordermanager_additional_after_reauthorization', ['additional' => $this]);
        $form = $this->getFormAfterReAuthorization();
        if (!empty($form) && is_array($form)) {
            return $form;
        }

        return $this->prepareResponse();
    }

    /**
     * @param $data
     * @return $this
     */
    public function setFormAfterReAuthorization($data)
    {
        $this->formAfterReAuthorization = $data;
        return $this;
    }

    /**
     * @return array|bool
     */
    public function getFormAfterReAuthorization()
    {
        return $this->formAfterReAuthorization;
    }

    /**
     * @return void
     */
    abstract protected function update();

    /**
     * @return string
     */
    abstract protected function prepareResponse();

    /**
     * @return bool
     */
    protected function needUpdatePaymentInfo()
    {
        return !$this->getPayment()->isAvailable()
            && !$this->getOrder()->hasInvoices();
    }

    /**
     * @return bool
     */
    protected function needUpdateShippingInfo()
    {
        return !$this->getOrder()->getIsVirtual()
        && ($this->getShipping()->isNotAvailable() || $this->getShipping()->isTotalChanged());
    }

    /**
     * @return void
     */
    public function doReAuthorization()
    {
        $order = $this->getOrder();
        $order->collectOrderTotals();
        $order->updatePayment();
    }

    /**
     * @return void
     */
    protected function prepareObjects()
    {
        $this->loadOrder();
        $quote = $this->loadQuote();
        $this->getShipping()->setQuote($quote);
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function formForUpdateShippingInfo()
    {
        return [
            'result' => 'additional_info',
            'form'   => $this->getShippingFormHtml(),
            'title'  => __('Shipping &amp; Handling Information'),
        ];
    }

    /**
     * @return bool
     */
    protected function isUpdateShippingInfoAutomatically()
    {
        return (boolean)$this->scopeConfig
            ->getValue('iwdordermanager/general/auto_apply_shipping')
            && !$this->getShipping()->isNotAvailable();
    }

    /**
     * @return void
     */
    protected function updateShippingInfo()
    {
        $this->shipping->recollectShippingAmount();
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getShippingFormHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /**
         * @var \IWD\OrderManager\Block\Adminhtml\Order\Additional\Shipping $shippingFormContainer
         */
        $shippingFormContainer = $resultPage->getLayout()
            ->getBlock('iwdordermamager_order_shipping_form');

        if (empty($shippingFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $shippingFormContainer
            ->setOrder($this->getOrder())
            ->setQuote($this->getQuote())
            ->setShipping($this->getShipping());

        return $shippingFormContainer->toHtml();
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    protected function formForUpdatePaymentInfo()
    {
        return [
            'result' => 'additional_info',
            'form'   => $this->getPaymentFormHtml(),
            'title'  => __('Payment Information'),
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getPaymentFormHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /**
         * @var \IWD\OrderManager\Block\Adminhtml\Order\Additional\Payment $paymentFormContainer
         */
        $paymentFormContainer = $resultPage->getLayout()->getBlock('iwdordermamager_order_payment_form');

        if (empty($paymentFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $paymentFormContainer
            ->setOrder($this->getOrder())
            ->setQuote($this->getQuote());

        return $paymentFormContainer->toHtml();
    }
}
