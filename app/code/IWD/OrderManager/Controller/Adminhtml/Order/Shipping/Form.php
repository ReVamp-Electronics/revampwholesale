<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Shipping;

use IWD\OrderManager\Model\Quote\Quote;
use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Form
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Shipping
 */
class Form extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_shipping';

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param Quote $quote
     * @param Order $order
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        Quote $quote,
        Order $order
    ) {
        parent::__construct($context, $resultPageFactory, $helper);
        $this->_quote = $quote;
        $this->_order = $order;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        /** @var \IWD\OrderManager\Block\Adminhtml\Order\Shipping\Form $shippingFormContainer */
        $shippingFormContainer = $this->resultPageFactory->create()
            ->getLayout()
            ->getBlock('iwdordermamager_order_shipping_form');
        if (empty($shippingFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $quote = $this->getQuote();
        $order = $this->getOrder();
        $shippingFormContainer->setQuote($quote);
        $shippingFormContainer->setOrder($order);

        return $shippingFormContainer->toHtml();
    }

    /**
     * @return Quote
     * @throws \Exception
     */
    protected function getQuote()
    {
        $quoteId = $this->getOrder()->getQuoteId();
        return $this->_quote->load($quoteId);
    }

    /**
     * @return Order
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->_order->load($orderId);
    }
}
