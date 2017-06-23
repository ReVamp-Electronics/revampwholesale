<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\Config\Source;

class Attributes
{
    /**
     * @var \Amasty\CustomerAttributes\Helper\Collection
     */
    protected $helper;

    public function __construct(
        \Amasty\CustomerAttributes\Helper\Collection $helper
    ) {
        $this->helper = $helper;
    }

    public function toOptionArray()
    {
        $hash      = $this->helper->getAttributesHash();
        $options   = [];
        $options[] = [
            'value' => '',
            'label' => __('- Magento Default (E-mail) -')
        ];
        foreach ($hash as $key => $option) {
            $options[] = ['value' => $key, 'label' => $option];
        }

        return $options;
    }
}
