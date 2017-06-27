<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Additional;

use IWD\OrderManager\Block\Adminhtml\Order\Shipping\Form as ShippingForm;

/**
 * Class Shipping
 * @package IWD\OrderManager\Block\Adminhtml\Order\Additional
 */
class Shipping extends ShippingForm
{
    /**
     * @return bool
     */
    public function isNotAvailable()
    {
        return $this->getShipping()->isNotAvailable();
    }

    /**
     * @return bool
     */
    public function isTotalChanged()
    {
        return $this->getShipping()->isTotalChanged();
    }

    /**
     * @return string
     */
    public function jsonParamsShipping()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_shipping/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_shipping/update'),
            'shippingMethodBlockId' => '#order-shipping-method-choose-additional',
            'initButtonForLoad' => false
        ];

        return json_encode($data);
    }
}
