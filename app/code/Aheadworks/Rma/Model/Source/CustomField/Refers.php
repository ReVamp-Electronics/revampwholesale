<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\CustomField;

class Refers implements \Magento\Framework\Option\ArrayInterface
{
    const REQUEST_VALUE     = 'request';
    const ITEM_VALUE        = 'item';

    const REQUEST_LABEL    = 'Request';
    const ITEM_LABEL       = 'Item';

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
            self::REQUEST_VALUE    => __(self::REQUEST_LABEL),
            self::ITEM_VALUE       => __(self::ITEM_LABEL)
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
