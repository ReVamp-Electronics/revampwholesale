<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source\Ticket;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class Status implements OptionSourceInterface
{
    /**
     * Status values
     */
    const OPEN_VALUE = 'open';
    const PENDING_VALUE = 'pending';
    const SOLVED_VALUE = 'solved';

    /**
     * Status labels
     */
    const OPEN_LABEL = 'Open';
    const PENDING_LABEL = 'Pending';
    const SOLVED_LABEL = 'Solved';

    const DEFAULT_STATUS = self::PENDING_VALUE;

    /**
     * Get option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::OPEN_VALUE => __(self::OPEN_LABEL),
            self::PENDING_VALUE => __(self::PENDING_LABEL),
            self::SOLVED_VALUE => __(self::SOLVED_LABEL)
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
            ['value' => self::OPEN_VALUE,  'label' => __(self::OPEN_LABEL)],
            ['value' => self::PENDING_VALUE,  'label' => __(self::PENDING_LABEL)],
            ['value' => self::SOLVED_VALUE,  'label' => __(self::SOLVED_LABEL)],
        ];
    }

    /**
     * Get option label
     *
     * @param $status
     * @return string
     */
    public function getOptionLabelByValue($status)
    {
        $statuses = $this->getOptionArray();
        $label = '';
        if (array_key_exists($status, $statuses)) {
            $label = $statuses[$status];
        }
        return $label;
    }

    /**
     * Get form option array
     * @return array
     */
    public function getFormOptionArray()
    {
        $options = $this->getOptionArray();
        unset($options[self::SOLVED_VALUE]);

        return $options;
    }
}
