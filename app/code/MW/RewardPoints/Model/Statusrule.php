<?php

namespace MW\RewardPoints\Model;

class Statusrule extends \Magento\Framework\DataObject
{
    const ENABLED = 1; // Haven't change points yet
    const DISABLED = 2;

    public static function getOptionArray()
    {
        return [
            self::ENABLED  => __('Enabled'),
            self::DISABLED => __('Disabled')
        ];
    }

    public static function getLabel($status)
    {
        $options = self::getOptionArray();

        return $options[$status];
    }
}
