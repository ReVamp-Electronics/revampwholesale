<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Automation;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Helpdesk\Model\Source\Automation\Event as AutomationEventSource;

/**
 * Class Action
 * @package Aheadworks\Helpdesk\Model\Source\Automation
 */
class Action implements OptionSourceInterface
{
    /**#@+
     * Action values
     */
    const SEND_CUSTOMER_EMAIL_VALUE = 'send_email_to_customer';
    const SEND_AGENT_EMAIL_VALUE    = 'send_email_to_agent';
    const CHANGE_STATUS_VALUE       = 'change_status';
    const CHANGE_PRIORITY_VALUE     = 'change_priority';
    const ASSIGN_TICKET_VALUE       = 'assign_ticket';
    const CHANGE_DEPARTMENT_VALUE   = 'change_department';
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
                'value' => self::SEND_CUSTOMER_EMAIL_VALUE,
                'label' => __('Send email to Customer')
            ],
            [
                'value' => self::SEND_AGENT_EMAIL_VALUE,
                'label' => __('Send email to Agent')
            ],
            [
                'value' => self::CHANGE_STATUS_VALUE,
                'label' => __('Change status to')
            ],
            [
                'value' => self::CHANGE_PRIORITY_VALUE,
                'label' => __('Change priority to')
            ],
            [
                'value' => self::ASSIGN_TICKET_VALUE,
                'label' => __('Assign ticket to')
            ],
            [
                'value' => self::CHANGE_DEPARTMENT_VALUE,
                'label' => __('Change department to')
            ]
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
                self::SEND_CUSTOMER_EMAIL_VALUE     => $options[self::SEND_CUSTOMER_EMAIL_VALUE],
                self::SEND_AGENT_EMAIL_VALUE        => $options[self::SEND_AGENT_EMAIL_VALUE],
                self::CHANGE_STATUS_VALUE           => $options[self::CHANGE_STATUS_VALUE],
                self::CHANGE_PRIORITY_VALUE         => $options[self::CHANGE_PRIORITY_VALUE],
                self::ASSIGN_TICKET_VALUE           => $options[self::ASSIGN_TICKET_VALUE],
                self::CHANGE_DEPARTMENT_VALUE       => $options[self::CHANGE_DEPARTMENT_VALUE],
            ],
            AutomationEventSource::NEW_AGENT_TICKET_VALUE => [
                self::SEND_CUSTOMER_EMAIL_VALUE     => $options[self::SEND_CUSTOMER_EMAIL_VALUE],
                self::SEND_AGENT_EMAIL_VALUE        => $options[self::SEND_AGENT_EMAIL_VALUE],
            ],
            AutomationEventSource::NEW_CUSTOMER_REPLY_VALUE => [
                self::SEND_CUSTOMER_EMAIL_VALUE     => $options[self::SEND_CUSTOMER_EMAIL_VALUE],
                self::SEND_AGENT_EMAIL_VALUE        => $options[self::SEND_AGENT_EMAIL_VALUE],
                self::CHANGE_STATUS_VALUE           => $options[self::CHANGE_STATUS_VALUE],
                self::CHANGE_PRIORITY_VALUE         => $options[self::CHANGE_PRIORITY_VALUE],
                self::ASSIGN_TICKET_VALUE           => $options[self::ASSIGN_TICKET_VALUE],
                self::CHANGE_DEPARTMENT_VALUE       => $options[self::CHANGE_DEPARTMENT_VALUE],
            ],
            AutomationEventSource::NEW_AGENT_REPLY_VALUE => [
                self::SEND_CUSTOMER_EMAIL_VALUE     => $options[self::SEND_CUSTOMER_EMAIL_VALUE],
                self::SEND_AGENT_EMAIL_VALUE        => $options[self::SEND_AGENT_EMAIL_VALUE],
            ],
            AutomationEventSource::RECURRING_TASK_VALUE => [
                self::SEND_CUSTOMER_EMAIL_VALUE     => $options[self::SEND_CUSTOMER_EMAIL_VALUE],
                self::SEND_AGENT_EMAIL_VALUE        => $options[self::SEND_AGENT_EMAIL_VALUE],
                self::CHANGE_STATUS_VALUE           => $options[self::CHANGE_STATUS_VALUE],
                self::CHANGE_PRIORITY_VALUE         => $options[self::CHANGE_PRIORITY_VALUE],
                self::ASSIGN_TICKET_VALUE           => $options[self::ASSIGN_TICKET_VALUE],
                self::CHANGE_DEPARTMENT_VALUE       => $options[self::CHANGE_DEPARTMENT_VALUE],
            ],
            AutomationEventSource::TICKET_ASSIGNED_VALUE => [
                self::SEND_CUSTOMER_EMAIL_VALUE     => $options[self::SEND_CUSTOMER_EMAIL_VALUE],
                self::SEND_AGENT_EMAIL_VALUE        => $options[self::SEND_AGENT_EMAIL_VALUE],
            ],
        ];
    }
}
