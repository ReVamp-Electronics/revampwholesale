<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Statuses implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            '0' => __('Inactive'),
            '1' => __('Active'),
        ];
    }
}
