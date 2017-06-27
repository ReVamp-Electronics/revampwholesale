<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Config\Source;

class Logic implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            '0' => __('Strings, e.g. AB2%'),
            '1'   => __('Numbers, e.g. from 111 to 222 or from AB2 to AB19')
        ];
    }
}
