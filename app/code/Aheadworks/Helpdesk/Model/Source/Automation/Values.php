<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Automation;

/**
 * Class Values
 * @package Aheadworks\Helpdesk\Model\Source\Automation
 */
class Values
{
    /**
     * Group repository
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * Email config
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * Email template collection factory
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    private $emailTemplateFactory;

    /**
     * Search criteria builder
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Object converter
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    /**
     * Ticket status source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    private $ticketStatusSource;

    /**
     * Ticket priority source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Priority
     */
    private $ticketPrioritySource;

    /**
     * Agent source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Agent
     */
    private $agentSource;

    /**
     * Department source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Department
     */
    private $departmentSource;

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepositoryInterface
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Department $departmentSource
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepositoryInterface,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Department $departmentSource
    ) {
        $this->groupRepository = $groupRepositoryInterface;
        $this->emailConfig = $emailConfig;
        $this->emailTemplateFactory = $templatesFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->ticketStatusSource = $statusSource;
        $this->ticketPrioritySource = $prioritySource;
        $this->agentSource = $agentSource;
        $this->departmentSource = $departmentSource;
    }

    /**
     * Get options array for condition
     * @return array
     */
    public function getAvailableOptionByConditionType()
    {
        $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $customerGroups = $this->objectConverter->toOptionHash($customerGroups, 'id', 'code');

        $ticketStatuses = $this->ticketStatusSource->getOptionArray();
        $departments = $this->departmentSource->getOptions();
        return [
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::CUSTOMER_GROUP_VALUE => [
                'multiselect' => $customerGroups
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TICKET_STATUS_VALUE => [
                'multiselect' => $ticketStatuses
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TICKET_DEPARTMENT_VALUE => [
                'multiselect' => $departments
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TICKET_SUBJECT_VALUE => [
                'text' => ''
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::FIRST_MESSAGE_CONTAINS_VALUE => [
                'text' => ''
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TOTAL_MESSAGES_VALUE => [
                'text' => ''
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TOTAL_AGENT_MESSAGES_VALUE => [
                'text' => ''
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::TOTAL_CUSTOMER_MESSAGES_VALUE => [
                'text' => ''
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::LAST_REPLIED_HOURS_VALUE => [
                'text' => ''
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Condition::LAST_REPLIED_BY_VALUE => [
                'select' => [
                    \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE => __('Customer'),
                    \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_VALUE => __('Admin'),
                ]
            ],
        ];
    }

    /**
     * Get options array for action
     * @return array
     */
    public function getAvailableOptionByActionType()
    {
        $emailTemplates = $this->emailTemplateFactory->create()->load()->toOptionArray();
        $availableTemplatesValues = array_merge($this->emailConfig->getAvailableTemplates(), $emailTemplates);
        $availableTemplates = [];
        foreach ($availableTemplatesValues as $availableTemplate) {
            $availableTemplates[$availableTemplate['value']] = $availableTemplate['label'];
        }
        $ticketStatuses = $this->ticketStatusSource->getOptionArray();
        $ticketPriorities = $this->ticketPrioritySource->getOptionArray();
        $agents = $this->agentSource->getAvailableOptions();
        $departments = $this->departmentSource->getOptions();
        return [
            \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE => [
                'select' => $availableTemplates
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_AGENT_EMAIL_VALUE => [
                'select' => $availableTemplates
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_STATUS_VALUE => [
                'select' => $ticketStatuses
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_PRIORITY_VALUE => [
                'select' => $ticketPriorities
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Action::ASSIGN_TICKET_VALUE => [
                'select' => $agents
            ],
            \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_DEPARTMENT_VALUE => [
                'select' => $departments
            ],
        ];
    }
}
