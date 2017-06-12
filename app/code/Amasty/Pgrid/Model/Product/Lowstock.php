<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Model\Product;

use Magento\Framework\Data\OptionSourceInterface;

class Lowstock extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    const YES = 1;
    const NO = 0;

    public static function getOptionArray()
    {
        return [
            self::YES => __('Yes'),
            self::NO => __('No')
        ];
    }

    public static function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}