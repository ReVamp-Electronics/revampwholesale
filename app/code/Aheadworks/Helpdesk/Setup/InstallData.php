<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Setup;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Aheadworks\Helpdesk\Model\Source\Websites as WebsitesSource;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Aheadworks\Helpdesk\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var WebsitesSource
     */
    private $websitesSource;

    /**
     * @param WebsitesSource $websitesSource
     */
    public function __construct(
        WebsitesSource $websitesSource
    ) {
        $this->websitesSource = $websitesSource;
    }

    /**
     * Install data
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addDefaultDepartment($setup);

        $newCustomerTicketConditions = [];
        $newCustomerTicketActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE,
                'value' => 'new_ticket_from_customer_email_to_customer'
            ],
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::ASSIGN_TICKET_VALUE,
                'value' => '0'
            ]
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 1,
                'name' => 'New ticket from Customer',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_CUSTOMER_TICKET_VALUE,
                'conditions' => serialize($newCustomerTicketConditions),
                'actions' => serialize($newCustomerTicketActions),
            ]
        );

        $newAgentTicketConditions = [];
        $newAgentTicketActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE,
                'value' => 'new_ticket_from_agent_to_customer'
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 2,
                'name' => 'New ticket from agent',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_AGENT_TICKET_VALUE,
                'conditions' => serialize($newAgentTicketConditions),
                'actions' => serialize($newAgentTicketActions),
            ]
        );

        $newCustomerReplyConditions = [];
        $newCustomerReplyActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_AGENT_EMAIL_VALUE,
                'value' => 'new_reply_from_customer'
            ],
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_STATUS_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 3,
                'name' => 'New reply from Customer',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_CUSTOMER_REPLY_VALUE,
                'conditions' => serialize($newCustomerReplyConditions),
                'actions' => serialize($newCustomerReplyActions),
            ]
        );

        $newAgentReplyConditions = [];
        $newAgentReplyActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE,
                'value' => 'new_reply_from_agent'
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 4,
                'name' => 'New reply from agent',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_AGENT_REPLY_VALUE,
                'conditions' => serialize($newAgentReplyConditions),
                'actions' => serialize($newAgentReplyActions),
            ]
        );

        $ticketAssignedConditions = [];
        $ticketAssignedActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_AGENT_EMAIL_VALUE,
                'value' => 'ticket_reassign'
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 5,
                'name' => 'Ticket was assigned to another agent',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::TICKET_ASSIGNED_VALUE,
                'conditions' => serialize($ticketAssignedConditions),
                'actions' => serialize($ticketAssignedActions),
            ]
        );

        $waitingTicketConditions = [
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TICKET_STATUS_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::IN_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE
            ],
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::LAST_REPLIED_HOURS_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::EQUALS_GREATER_THAN_VALUE,
                'value' => '24'
            ],
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::LAST_REPLIED_BY_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::EQUALS_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE
            ],
        ];
        $waitingTicketActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_PRIORITY_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Priority::HIGH_VALUE
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 6,
                'name' => 'Change priority of an open ticket that is waiting for reply for 24+ hours',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::RECURRING_TASK_VALUE,
                'conditions' => serialize($waitingTicketConditions),
                'actions' => serialize($waitingTicketActions),
            ]
        );

        $leftMessagesConditions = [
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TICKET_STATUS_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::IN_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE
            ],
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TOTAL_AGENT_MESSAGES_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::EQUALS_VALUE,
                'value' => '0'
            ],
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TOTAL_CUSTOMER_MESSAGES_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::EQUALS_GREATER_THAN_VALUE,
                'value' => '3'
            ],
        ];
        $leftMessagesActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_PRIORITY_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Priority::HIGH_VALUE
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 7,
                'name' => 'Change priority of a new ticket where customer left 3+ messages',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::RECURRING_TASK_VALUE,
                'conditions' => serialize($leftMessagesConditions),
                'actions' => serialize($leftMessagesActions),
            ]
        );

        $followupConditions = [
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TICKET_STATUS_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::IN_VALUE,
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::PENDING_VALUE
            ],
            [
                'object' => \Aheadworks\Helpdesk\Model\Source\Automation\Condition::LAST_REPLIED_HOURS_VALUE,
                'operator' => \Aheadworks\Helpdesk\Model\Source\Automation\Operator::EQUALS_GREATER_THAN_VALUE,
                'value' => '48'
            ],
        ];
        $followupActions = [
            [
                'action' => \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE,
                'value' => 'automatic_followup_to_customer'
            ],
        ];
        $setup->getConnection()->insertForce(
            $setup->getTable('aw_helpdesk_automation'),
            [
                'id' => 8,
                'name' => 'Follow-up to customer to check if customer requires further assistance',
                'status' => \Aheadworks\Helpdesk\Model\Source\Automation\Status::ACTIVE_VALUE,
                'priority' => 0,
                'event' => \Aheadworks\Helpdesk\Model\Source\Automation\Event::RECURRING_TASK_VALUE,
                'conditions' => serialize($followupConditions),
                'actions' => serialize($followupActions),
            ]
        );
        $setup->endSetup();
    }

    /**
     * Add default gateway
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addDefaultDepartment(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->insert(
            $setup->getTable('aw_helpdesk_department'),
            [
                'id' => 1,
                'name' => 'General',
                'is_enabled' => 1,
                'is_visible' => 0,
                'is_default' => 1
            ]
        );

        $select = $setup->getConnection()->select()
            ->from($setup->getTable('aw_helpdesk_department'), ['id'])
            ->where('name = :name');
        $defaultDepartmentId = $setup->getConnection()->fetchOne($select, [':name' => 'General']);

        if ($defaultDepartmentId) {
            $websiteRows = [];
            $websites = $this->websitesSource->toOptionArray();
            foreach ($websites as $website) {
                $websiteRows[] = [
                    'department_id' => $defaultDepartmentId,
                    'website_id' => $website['value']
                ];
            }
            if (count($websiteRows) > 0) {
                $setup->getConnection()->insertMultiple(
                    $setup->getTable('aw_helpdesk_department_website'),
                    $websiteRows
                );
            }

            $permissionRows = [];
            $permissionRows[] = [
                'department_id' => $defaultDepartmentId,
                'role_id' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'type' => DepartmentPermissionInterface::TYPE_VIEW
            ];
            $permissionRows[] = [
                'department_id' => $defaultDepartmentId,
                'role_id' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'type' => DepartmentPermissionInterface::TYPE_UPDATE
            ];
            $permissionRows[] = [
                'department_id' => $defaultDepartmentId,
                'role_id' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'type' => DepartmentPermissionInterface::TYPE_ASSIGN
            ];
        }
        if (count($permissionRows) > 0) {
            $setup->getConnection()->insertMultiple(
                $setup->getTable('aw_helpdesk_department_permission'),
                $permissionRows
            );
        }

        return $this;
    }
}
