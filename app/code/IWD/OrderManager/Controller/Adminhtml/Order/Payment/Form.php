<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Payment;

use IWD\OrderManager\Model\Quote\Quote;
use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Payment
 */
class Form extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_payment';

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $sessionQuote;

    /**
     * @var \IWD\OrderManager\Model\Rewrite\Session\Quote
     */
    private $savedSessionQuote;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param Quote $quote
     * @param Order $order
     * @param \IWD\OrderManager\Model\Rewrite\Session\Quote $sessionQuote
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        Quote $quote,
        Order $order,
        \IWD\OrderManager\Model\Rewrite\Session\Quote $sessionQuote
    ) {
        parent::__construct($context, $resultPageFactory, $helper);
        $this->quote = $quote;
        $this->order = $order;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $this->sessionQuote->getQuote();
        $this->saveSessionQuote();
        $this->loadNewSessionQuote();

        $formHtml = $this->getPaymentEditForm();

        $this->restoreSessionQuote();

        return $formHtml;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->sessionQuote;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->order->load($orderId);
    }

    /**
     * @return void
     */
    private function saveSessionQuote()
    {
        $this->savedSessionQuote = $this->sessionQuote->getQuote();
    }

    /**
     * @return void
     */
    private function loadNewSessionQuote()
    {
        $order = $this->getOrder();
        $quoteId = $order->getQuoteId();
        $storeId = $order->getStoreId();
        $currencyId = $order->getOrderCurrencyCode();
        $customerId = $order->getCustomerId();

        $this->sessionQuote->clearQuoteParams();

        $this->sessionQuote->setQuoteId($quoteId);
        $this->sessionQuote->setStoreId($storeId);
        $this->sessionQuote->setCurrencyId($currencyId);
        $this->sessionQuote->setCustomerId($customerId);
        $this->sessionQuote->getQuote();
    }

    /**
     * @return void
     */
    private function restoreSessionQuote()
    {
        $quoteId = $this->savedSessionQuote->getQuoteId();
        $storeId = $this->savedSessionQuote->getStoreId();
        $currencyId = $this->savedSessionQuote->getCurrencyId();
        $customerId = $this->savedSessionQuote->getCustomerId();

        $this->sessionQuote->clearQuoteParams();

        $this->sessionQuote->setQuoteId($quoteId);
        $this->sessionQuote->setStoreId($storeId);
        $this->sessionQuote->setCurrencyId($currencyId);
        $this->sessionQuote->setCustomerId($customerId);
        $this->sessionQuote->getQuote();
    }

    /**
     * @return string
     */
    private function getPaymentEditForm()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('sales_order_create_load_block_billing_method');
        $paymentHtml = $resultPage->getLayout()->renderElement('content');

        return '<form id="order-billing_method">' . $paymentHtml . '</form>';
    }
}
