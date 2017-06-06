<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Automation;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Helpdesk\Model\Source\Automation\Event as AutomationEventSource;

/**
 * Class Condition
 * @package Aheadworks\Helpdesk\Model\Source\Automation
 */
class Condition implements OptionSourceInterface
{
    /**#@+
     * Condition values
     */
    const CUSTOMER_GROUP_VALUE          = 'customer.group_id';
    const TICKET_STATUS_VALUE           = 'main_table.status';
    const TICKET_DEPARTMENT_VALUE       = 'main_table.department_id';
    const TICKET_SUBJECT_VALUE          = 'main_table.subject';
    const FIRST_MESSAGE_CONTAINS_VALUE  = 'flat.first_message_content';
    const TOTAL_MESSAGES_VALUE          = '(flat.customer_messages + flat.agent_messages)';
    const TOTAL_AGENT_MESSAGES_VALUE    = 'flat.agent_messages';
    const TOTAL_CUSTOMER_MESSAGES_VALUE = 'flat.customer_messages';
    const LAST_REPLIED_HOURS_VALUE      = 'flat.last_reply_date';
    const LAST_REPLIED_BY_VALUE         = 'flat.last_reply_type';
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
                'value' => self::CUSTOMER_GROUP_VALUE,
                'label' => __('Customer group')
            ],
            [
                'value' => self::TICKET_STATUS_VALUE,
                'label' => __('Ticket status')
            ],
            [
                'value' => self::TICKET_DEPARTMENT_VALUE,
                'label' => __('Ticket department')
            ],
            [
                'value' => self::TICKET_SUBJECT_VALUE,
                'label' => __('Subject contains')
            ],
            [
                'value' => self::FIRST_MESSAGE_CONTAINS_VALUE,
                'label' => __('First message contains')
            ],
            [
                'value' => self::TOTAL_MESSAGES_VALUE,
                'label' => __('Total messages')
            ],
            [
                'value' => self::TOTAL_AGENT_MESSAGES_VALUE,
                'label' => __("Total Agents' messages")
            ],
            [
                'value' => self::TOTAL_CUSTOMER_MESSAGES_VALUE,
                'label' => __("Total Customer's messages")
            ],
            [
                'value' => self::LAST_REPLIED_HOURS_VALUE,
                'label' => __('Last replied X hours ago')
            ],
            [
                'value' => self::LAST_REPLIED_BY_VALUE,
                'label' => __('Last replied by')
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
    public function getAvailableOptionByEventType()
    {
        $options = $this->getOptionArray();
        return [
            AutomationEventSource::NEW_CUSTOMER_TICKET_VALUE => [
                self::CUSTOMER_GROUP_VALUE          => $options[self::CUSTOMER_GROUP_VALUE],
                self::TICKET_DEPARTMENT_VALUE       => $options[self::TICKET_DEPARTMENT_VALUE],
                self::TICKET_SUBJECT_VALUE          => $options[self::TICKET_SUBJECT_VALUE],
                self::FIRST_MESSAGE_CONTAINS_VALUE  => $options[self::FIRST_MESSAGE_CONTAINS_VALUE],
            ],
            AutomationEventSource::NEW_AGENT_TICKET_VALUE => [
                self::CUSTOMER_GROUP_VALUE          => $options[self::CUSTOMER_GROUP_VALUE],
                self::TICKET_DEPARTMENT_VALUE       => $options[self::TICKET_DEPARTMENT_VALUE],
                self::TICKET_SUBJECT_VALUE          => $options[self::TICKET_SUBJECT_VALUE],
                self::FIRST_MESSAGE_CONTAINS_VALUE  => $options[self::FIRST_MESSAGE_CONTAINS_VALUE],
            ],
            AutomationEventSource::NEW_CUSTOMER_REPLY_VALUE => [
                self::CUSTOMER_GROUP_VALUE          => $options[self::CUSTOMER_GROUP_VALUE],
                self::TICKET_STATUS_VALUE           => $options[self::TICKET_STATUS_VALUE],
                self::TICKET_DEPARTMENT_VALUE       => $options[self::TICKET_DEPARTMENT_VALUE],
                self::TOTAL_MESSAGES_VALUE          => $options[self::TOTAL_MESSAGES_VALUE],
                self::TOTAL_AGENT_MESSAGES_VALUE    => $options[self::TOTAL_AGENT_MESSAGES_VALUE],
                self::TOTAL_CUSTOMER_MESSAGES_VALUE => $options[self::TOTAL_CUSTOMER_MESSAGES_VALUE],
                self::LAST_REPLIED_HOURS_VALUE      => $options[self::LAST_REPLIED_HOURS_VALUE],
                self::LAST_REPLIED_BY_VALUE         => $options[self::LAST_REPLIED_BY_VALUE],
            ],
            AutomationEventSource::NEW_AGENT_REPLY_VALUE => [
                self::CUSTOMER_GROUP_VALUE          => $options[self::CUSTOMER_GROUP_VALUE],
                self::TICKET_STATUS_VALUE           => $options[self::TICKET_STATUS_VALUE],
                self::TICKET_DEPARTMENT_VALUE       => $options[self::TICKET_DEPARTMENT_VALUE],
                self::TOTAL_MESSAGES_VALUE          => $options[self::TOTAL_MESSAGES_VALUE],
                self::TOTAL_AGENT_MESSAGES_VALUE    => $options[self::TOTAL_AGENT_MESSAGES_VALUE],
                self::TOTAL_CUSTOMER_MESSAGES_VALUE => $options[self::TOTAL_CUSTOMER_MESSAGES_VALUE],
                self::LAST_REPLIED_HOURS_VALUE      => $options[self::LAST_REPLIED_HOURS_VALUE],
                self::LAST_REPLIED_BY_VALUE         => $options[self::LAST_REPLIED_BY_VALUE],
            ],
            AutomationEventSource::RECURRING_TASK_VALUE => [
                self::CUSTOMER_GROUP_VALUE          => $options[self::CUSTOMER_GROUP_VALUE],
                self::TICKET_STATUS_VALUE           => $options[self::TICKET_STATUS_VALUE],
                self::TICKET_DEPARTMENT_VALUE       => $options[self::TICKET_DEPARTMENT_VALUE],
                self::TOTAL_MESSAGES_VALUE          => $options[self::TOTAL_MESSAGES_VALUE],
                self::TOTAL_AGENT_MESSAGES_VALUE    => $options[self::TOTAL_AGENT_MESSAGES_VALUE],
                self::TOTAL_CUSTOMER_MESSAGES_VALUE => $options[self::TOTAL_CUSTOMER_MESSAGES_VALUE],
                self::LAST_REPLIED_HOURS_VALUE      => $options[self::LAST_REPLIED_HOURS_VALUE],
                self::LAST_REPLIED_BY_VALUE         => $options[self::LAST_REPLIED_BY_VALUE],
            ],
            AutomationEventSource::TICKET_ASSIGNED_VALUE => [
                self::CUSTOMER_GROUP_VALUE          => $options[self::CUSTOMER_GROUP_VALUE],
                self::TICKET_STATUS_VALUE           => $options[self::TICKET_STATUS_VALUE],
                self::TICKET_DEPARTMENT_VALUE       => $options[self::TICKET_DEPARTMENT_VALUE],
                self::TOTAL_MESSAGES_VALUE          => $options[self::TOTAL_MESSAGES_VALUE],
                self::TOTAL_AGENT_MESSAGES_VALUE    => $options[self::TOTAL_AGENT_MESSAGES_VALUE],
                self::TOTAL_CUSTOMER_MESSAGES_VALUE => $options[self::TOTAL_CUSTOMER_MESSAGES_VALUE],
                self::LAST_REPLIED_HOURS_VALUE      => $options[self::LAST_REPLIED_HOURS_VALUE],
                self::LAST_REPLIED_BY_VALUE         => $options[self::LAST_REPLIED_BY_VALUE],
            ],
        ];
    }
}
