<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model\System\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    const DISLPAY_MODE_NAMES        = 0;
    const DISPLAY_MODE_NAMES_FLAGS  = 1;
    
    /**
     * Return options for config "Switcher scope"
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DISLPAY_MODE_NAMES, 'label' => __('Names')],
            ['value' => self::DISPLAY_MODE_NAMES_FLAGS, 'label' => __('Flags + Names')],
        ];
    }
}
