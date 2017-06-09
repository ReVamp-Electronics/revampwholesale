<?php

namespace IWD\AuthCIM\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PaymentAction
 */
class PaymentAction implements ArrayInterface
{
    /**
     * Authorize
     */
    const ACTION_AUTHORIZE = 'authorize';

    /**
     * Authorize and Capture
     */
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    /**
     * Possible actions on order place
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => self::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
