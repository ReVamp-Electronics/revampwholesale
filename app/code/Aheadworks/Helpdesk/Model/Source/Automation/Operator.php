<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Automation;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Helpdesk\Model\Source\Automation\Condition as AutomationConditionSource;

/**
 * Class Operator
 * @package Aheadworks\Helpdesk\Model\Source\Automation
 */
class Operator implements OptionSourceInterface
{
    /**#@+
     * Operator values
     */
    const LESS_THAN_VALUE           = 'lt';
    const EQUALS_LESS_THAN_VALUE    = 'lteq';
    const EQUALS_VALUE              = 'eq';
    const EQUALS_GREATER_THAN_VALUE = 'gteq';
    const GREATER_THAN_VALUE        = 'gt';

    const LIKE_VALUE                = 'like';
    const FIND_IN_SET               = 'finset';
    const IN_VALUE                  = 'in';
    /**#@-*/

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::LESS_THAN_VALUE,
                'label' => __('less than')
            ],
            [
                'value' => self::EQUALS_LESS_THAN_VALUE,
                'label' => __('equals or less than')
            ],
            [
                'value' => self::EQUALS_VALUE,
                'label' => __('equals')
            ],
            [
                'value' => self::EQUALS_GREATER_THAN_VALUE,
                'label' => __('equals or greater than')
            ],
            [
                'value' => self::GREATER_THAN_VALUE,
                'label' => __('greater than')
            ],
        ];
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = $this->toOptionArray();
        $result = [];
        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }

    /**
     * Get options array for event type
     * @return array
     */
    public function getAvailableOptionByConditionType()
    {
        return [
            AutomationConditionSource::CUSTOMER_GROUP_VALUE             => self::IN_VALUE,
            AutomationConditionSource::TICKET_STATUS_VALUE              => self::IN_VALUE,
            AutomationConditionSource::TICKET_DEPARTMENT_VALUE          => self::IN_VALUE,
            AutomationConditionSource::TICKET_SUBJECT_VALUE             => self::LIKE_VALUE,
            AutomationConditionSource::FIRST_MESSAGE_CONTAINS_VALUE     => self::LIKE_VALUE,
            AutomationConditionSource::TOTAL_MESSAGES_VALUE             => $this->getOptionArray(),
            AutomationConditionSource::TOTAL_AGENT_MESSAGES_VALUE       => $this->getOptionArray(),
            AutomationConditionSource::TOTAL_CUSTOMER_MESSAGES_VALUE    => $this->getOptionArray(),
            AutomationConditionSource::LAST_REPLIED_HOURS_VALUE         => $this->getOptionArray(),
            AutomationConditionSource::LAST_REPLIED_BY_VALUE            => self::EQUALS_VALUE,
        ];
    }
}
