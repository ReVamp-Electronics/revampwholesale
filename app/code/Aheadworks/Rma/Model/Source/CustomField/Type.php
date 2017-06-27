<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\CustomField;

/**
 * Class Type
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    const TEXT_VALUE            = 'text';
    const TEXT_AREA_VALUE       = 'textarea';
    const SELECT_VALUE          = 'select';
    const MULTI_SELECT_VALUE    = 'multiselect';

    const TEXT_LABEL            = 'Text Field';
    const TEXT_AREA_LABEL       = 'Text Area';
    const SELECT_LABEL          = 'Dropdown';
    const MULTI_SELECT_LABEL    = 'Multiselect';

    /**
     * @var null|array
     */
    protected $optionArray = null;

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            self::TEXT_VALUE            => __(self::TEXT_LABEL),
            self::TEXT_AREA_VALUE       => __(self::TEXT_AREA_LABEL),
            self::SELECT_VALUE          => __(self::SELECT_LABEL),
            self::MULTI_SELECT_VALUE    => __(self::MULTI_SELECT_LABEL)
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->optionArray === null) {
            $this->optionArray = [];
            foreach ($this->getOptions() as $value => $label) {
                $this->optionArray[] = ['value' => $value, 'label' => $label];
            }
        }
        return $this->optionArray;
    }

    /**
     * @param int $value
     * @return null|\Magento\Framework\Phrase
     */
    public function getOptionLabelByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
