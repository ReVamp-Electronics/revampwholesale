<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model\System\Config\Source\Switcher;

class Scope implements \Magento\Framework\Option\ArrayInterface
{
    const SCOPE_GLOBAL = 0;
    const SCOPE_WEBSITE = 1;
    
    /**
     * Return options for config "Switcher scope"
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SCOPE_GLOBAL, 'label' => __('Global')],
            ['value' => self::SCOPE_WEBSITE, 'label' => __('Website')],
        ];
    }
}
