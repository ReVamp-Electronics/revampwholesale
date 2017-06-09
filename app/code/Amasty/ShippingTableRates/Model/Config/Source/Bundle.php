<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Config\Source;

class Bundle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $vals = array(
            '0' => __('As in "Ship Bundle Items" setting'),
            '1'   => __('From bundle product'),
            '2'   => __('From items in bundle'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                    'value' => $k,
                    'label' => $v
            );
        
        return $options;
    }
}
