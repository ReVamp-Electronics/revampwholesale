<?php

namespace IWD\AuthCIM\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class SaveCC
 * @package IWD\AuthCIM\Model\Config\Source
 */
class SaveCC implements ArrayInterface
{
    /**
     * Allow customer to choose: save CC or now
     */
    const CAN_SELECT = 'choice';

    /**
     * Does not allow customer to choose. Save in any cases.
     */
    const SAVE_ALWAYS = 'save';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CAN_SELECT, 'label' => __('Customer Can Choose')],
            ['value' => self::SAVE_ALWAYS, 'label' => __('Save Always')],
        ];
    }
}
