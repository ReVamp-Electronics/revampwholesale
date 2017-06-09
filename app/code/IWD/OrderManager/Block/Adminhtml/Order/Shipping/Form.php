<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Shipping;

use IWD\OrderManager\Block\Adminhtml\Order\AbstractForm;
use IWD\OrderManager\Model\Quote\Quote;
use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Model\Order\Shipping as ShippingModel;
use Magento\Backend\Block\Template\Context;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\Shipping
 */
class Form extends AbstractForm
{
    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var ShippingModel
     */
    protected $shipping;

    /**
     * @param Context $context
     * @param ShippingModel $shipping
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingModel $shipping,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shipping = $shipping;
    }

    /**
     * @return ShippingModel
     */
    public function getShipping()
    {
        $this->shipping->setQuote($this->getQuote());
        return $this->shipping;
    }

    /**
     * @param ShippingModel $shipping
     * @return $this
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return void
     */
    protected function reloadShippingRates()
    {
        $this->getShipping()->setOrder($this->getOrder());
        $this->getShipping()->reloadShippingRates();
    }

    /**
     * @return string
     */
    public function getShippingForm()
    {
        $this->reloadShippingRates();

        /**
         * @var \IWD\OrderManager\Block\Adminhtml\Order\Shipping\Method $shippingMethodForm
         */
        $shippingMethodForm = $this->getChildBlock('shipping_method');

        if ($shippingMethodForm) {
            $shippingMethodForm->setQuote($this->getQuote());
            $shippingMethodForm->setOrder($this->getOrder());

            return $shippingMethodForm->toHtml();
        }

        return '';
    }
}
