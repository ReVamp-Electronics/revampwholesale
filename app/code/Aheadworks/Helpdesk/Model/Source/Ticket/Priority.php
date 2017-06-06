<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source\Ticket;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Priority
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class Priority implements OptionSourceInterface
{
    /**
     * Priority values
     */
    const HIGH_VALUE = 'high';
    const NORMAL_VALUE = 'normal';
    const LOW_VALUE = 'low';

    const DEFAULT_VALUE = self::NORMAL_VALUE;

    /**
     * Priority labels
     */
    const HIGH_LABEL = 'High';
    const NORMAL_LABEL = 'Normal';
    const LOW_LABEL = 'Low';

    /**
     * Get option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::HIGH_VALUE => __(self::HIGH_LABEL),
            self::NORMAL_VALUE => __(self::NORMAL_LABEL),
            self::LOW_VALUE => __(self::LOW_LABEL)
        ];
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::HIGH_VALUE,  'label' => __(self::HIGH_LABEL)],
            ['value' => self::NORMAL_VALUE,  'label' => __(self::NORMAL_LABEL)],
            ['value' => self::LOW_VALUE,  'label' => __(self::LOW_LABEL)],
        ];
    }

    /**
     * Get option label
     *
     * @param string $priority
     * @return string
     */
    public function getOptionLabelByValue($priority)
    {
        $priorities = $this->getOptionArray();
        $label = '';
        if (array_key_exists($priority, $priorities)) {
            $label = $priorities[$priority];
        }
        return $label;
    }
}
