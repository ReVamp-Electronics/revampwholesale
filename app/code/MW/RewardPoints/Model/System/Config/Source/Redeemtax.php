<?php

namespace MW\RewardPoints\Model\System\Config\Source;

class Redeemtax extends \Magento\Framework\DataObject
{
    const AFTER  = 1;
    const BEFORE = 2;

    public static function toOptionArray()
    {
        return [
            self::BEFORE => __('Before Redeempoint'),
            self::AFTER  => __('After Redeempoint')
        ];
    }
}
