<?php

namespace IWD\OrderManager\Model\Config\Source\Order;

use \Magento\Framework\Option\ArrayInterface;

class Totals implements ArrayInterface
{
    /**
     * Options getter
     * @return string[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'tax'     , 'label' => __('Tax')],
            ['value' => 'invoiced', 'label' => __('Invoiced')],
            ['value' => 'shipped' , 'label' => __('Shipping')],
            ['value' => 'refunded', 'label' => __('Refunds')],
            ['value' => 'discount', 'label' => __('Coupons')]
        ];
    }
}
