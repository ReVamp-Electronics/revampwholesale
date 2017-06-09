<?php

namespace MW\RewardPoints\Model;

class Action extends \Magento\Framework\DataObject
{
    const ADDITION    = 1;
    const SUBTRACTION = -1;

    public static function getOptionArray()
    {
        return [
            self::SUBTRACTION => __('Subtraction'),
            self::ADDITION    => __('Addition')
        ];
    }
}
