<?php

namespace MW\RewardPoints\Model;

class Status extends \Magento\Framework\DataObject
{
	const PENDING = 1; // Haven't change points yet
    const COMPLETE   = 2;
    const UNCOMPLETE = 0;
    const REFUNDED = 3; // Refunded

    public static function getOptionArray()
    {
        return [
            self::PENDING    => __('Pending'),
            self::COMPLETE   => __('Complete'),
            self::UNCOMPLETE => __('Cancelled'),
        ];
    }

    public static function getLabel($type)
    {
        $options = self::getOptionArray();

        return $options[$type];
    }
}
