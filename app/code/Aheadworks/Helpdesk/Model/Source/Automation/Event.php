<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Automation;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Event
 * @package Aheadworks\Helpdesk\Model\Source\Automation
 */
class Event implements OptionSourceInterface
{
    /**#@+
     * Event values
     */
    const NEW_CUSTOMER_TICKET_VALUE = 'new_ticket_from_customer';
    const NEW_AGENT_TICKET_VALUE    = 'new_ticket_from_agent';
    const NEW_CUSTOMER_REPLY_VALUE  = 'new_reply_from_customer';
    const NEW_AGENT_REPLY_VALUE     = 'new_reply_from_agent';
    const TICKET_ASSIGNED_VALUE     = 'ticket_assigned_to_another_agent';
    const RECURRING_TASK_VALUE      = 'recurring_task';
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
                'value' => self::NEW_CUSTOMER_TICKET_VALUE,
                'label' => __('New ticket from customer')
            ],
            [
                'value' => self::NEW_AGENT_TICKET_VALUE,
                'label' => __('New ticket from agent')
            ],
            [
                'value' => self::NEW_CUSTOMER_REPLY_VALUE,
                'label' => __('New reply from сustomer')
            ],
            [
                'value' => self::NEW_AGENT_REPLY_VALUE,
                'label' => __('New reply from agent')
            ],
            [
                'value' => self::TICKET_ASSIGNED_VALUE,
                'label' => __('Ticket was assigned to another agent')
            ],
            [
                'value' => self::RECURRING_TASK_VALUE,
                'label' => __('Recurring Task')
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
}
