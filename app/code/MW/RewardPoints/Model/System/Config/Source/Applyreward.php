<?php

namespace MW\RewardPoints\Model\System\Config\Source;

class Applyreward extends \Magento\Framework\DataObject
{
    const BEFORE = 1;
    const AFTER  = 2;

    public static function toOptionArray()
    {
        return [
            self::BEFORE => __('Before Discount'),
            self::AFTER  => __('After Discount')
        ];
    }
}
