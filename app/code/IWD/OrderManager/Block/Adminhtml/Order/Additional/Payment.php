<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Additional;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Block\Template\Context;
use IWD\OrderManager\Model\Rewrite\Session\Quote as SessionQuote;

/**
 * Class Payment
 * @package IWD\OrderManager\Block\Adminhtml\Order\Additional
 */
class Payment extends AbstractForm
{
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
     * @param SessionQuote $sessionQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionQuote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $resultPageFactory, $data);

        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @return string
     */
    public function getPaymentEditForm()
    {
        $this->sessionQuote->getQuote();
        $this->saveSessionQuote();
        $this->loadNewSessionQuote();

        $formHtml = $this->getPaymentForm();

        $this->restoreSessionQuote();

        return $formHtml;
    }

    /**
     * Save Session Quote
     */
    protected function saveSessionQuote()
    {
        $this->savedSessionQuote = $this->sessionQuote->getQuote();
    }

    /**
     * @return string
     */
    protected function getPaymentForm()
    {
        /**
         * @var \Magento\Framework\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('sales_order_create_load_block_billing_method');

        return $resultPage->getLayout()->renderElement('billing_method');
    }

    /**
     * @return void
     */
    protected function loadNewSessionQuote()
    {
        $order = $this->getOrder();
        $quoteId = $order->getQuoteId();
        $storeId = $order->getStoreId();
        $currencyId = $order->getOrderCurrencyCode();

        $this->sessionQuote->clearQuoteParams();

        $this->sessionQuote->setQuoteId($quoteId);
        $this->sessionQuote->setStoreId($storeId);
        $this->sessionQuote->setCurrencyId($currencyId);
        $this->sessionQuote->getQuote();
    }

    /**
     * @return void
     */
    protected function restoreSessionQuote()
    {
        $quoteId = $this->savedSessionQuote->getQuoteId();
        $storeId = $this->savedSessionQuote->getStoreId();
        $currencyId = $this->savedSessionQuote->getCurrencyId();

        $this->sessionQuote->clearQuoteParams();

        $this->sessionQuote->setQuoteId($quoteId);
        $this->sessionQuote->setStoreId($storeId);
        $this->sessionQuote->setCurrencyId($currencyId);
        $this->sessionQuote->getQuote();
    }

    /**
     * Get payment block params in json format
     * @return string
     */
    public function jsonParamsPayment()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_payment/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_payment/update'),
            'paymentMethodBlockId' => '#order-payment-method-choose-additional',
            'initButtonForLoad' => false
        ];

        return json_encode($data);
    }
}
