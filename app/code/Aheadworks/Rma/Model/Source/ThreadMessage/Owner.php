<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\ThreadMessage;

class Owner implements \Magento\Framework\Option\ArrayInterface
{
    const ADMIN_VALUE       = '1';
    const CUSTOMER_VALUE    = '2';

    const ADMIN_LABEL       = 'Admin';
    const CUSTOMER_LABEL    = 'Customer';

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
            self::ADMIN_VALUE       => __(self::ADMIN_LABEL),
            self::CUSTOMER_VALUE    => __(self::CUSTOMER_LABEL)
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
