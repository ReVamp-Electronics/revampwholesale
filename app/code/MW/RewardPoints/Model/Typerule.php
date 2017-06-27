<?php

namespace MW\RewardPoints\Model;

class Typerule extends \Magento\Framework\DataObject
{
    const FIXED = 1; // Haven't change points yet
    const BUY_X_GET_Y            = 2;
    const FIXED_WHOLE_CART       = 3;
    const BUY_X_GET_Y_WHOLE_CART = 4;

    public static function getOptionArray()
    {
        return [
            self::FIXED       => __('Fixed Reward Points (X)'),
            self::BUY_X_GET_Y => __('Spend Y Get X Reward Points'),
        ];
    }

    public static function getOptionArrayCart()
    {
        return [
            self::FIXED                  => __('Fixed Reward Points (X)'),
            self::FIXED_WHOLE_CART       => __('Fixed Reward Points (X) for Whole Cart'),
            self::BUY_X_GET_Y            => __('Spend Y Get X Reward Points'),
            self::BUY_X_GET_Y_WHOLE_CART => __('Spend Y Get X Reward Points for Whole Cart'),
        ];
    }

    public static function getLabel($status)
    {
        $options = self::getOptionArray();

        return $options[$status];
    }
}
