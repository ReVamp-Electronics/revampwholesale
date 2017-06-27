<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Model\Product;

use Magento\Framework\Data\OptionSourceInterface;

class Availability extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    const IN_STOCK = 1;
    const OUT_OF_STOCK = 0;

    public static function getOptionArray()
    {
        return [
            self::IN_STOCK => __('In Stock'),
            self::OUT_OF_STOCK => __('Ouf Of Stock')
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