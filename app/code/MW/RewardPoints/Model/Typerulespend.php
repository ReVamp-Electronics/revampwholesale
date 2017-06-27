<?php

namespace MW\RewardPoints\Model;

class Typerulespend extends \Magento\Framework\DataObject
{
    const FIXED = 1; // Haven't change points yet
    const BUY_X_USE_Y          = 2;
    const USE_UNLIMIT_POINTS   = 3;
    const NOT_ALLOW_USE_POINTS = 4;

    public static function getOptionArray()
    {
        return [
            self::NOT_ALLOW_USE_POINTS => __('Do Not Allow to Use Reward Points'),
            self::USE_UNLIMIT_POINTS   => __('Allow to Use Unlimited Points'),
            self::FIXED                => __('Allow to use fixed Reward Points (X) per order'),
            self::BUY_X_USE_Y          => __('Spend (Y) to allow to use Reward Points (X)'),
        ];
    }

    public static function getLabel($status)
    {
        $options = self::getOptionArray();

        return $options[$status];
    }
}
