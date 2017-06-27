<?php

namespace MW\RewardPoints\Model\System\Config\Source;

class Position extends \Magento\Framework\DataObject
{
	const BEFORE = 1;
    const AFTER  = 2;

    public static function toOptionArray()
    {
        return [
            self::BEFORE => __('Before Point Value'),
            self::AFTER  => __('After Point Value')
        ];
    }
}
