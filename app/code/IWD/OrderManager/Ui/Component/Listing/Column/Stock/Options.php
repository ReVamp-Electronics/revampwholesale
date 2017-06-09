<?php

namespace IWD\OrderManager\Ui\Component\Listing\Column\Stock;

use \Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    const NOT_ASSIGNED = 0;

    const ASSIGNED = 1;

    const NOT_APPLICABLE = -1;

    const ORDER_PLACED_BEFORE = -2;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NOT_ASSIGNED,  'label' => __('Not Assigned')],
            ['value' => self::ASSIGNED,  'label' => __('Assigned')],
            ['value' => self::NOT_APPLICABLE, 'label' => __('Not Applicable')],
            ['value' => self::ORDER_PLACED_BEFORE,  'label' => __('Order Placed Before')]
        ];
    }
}
