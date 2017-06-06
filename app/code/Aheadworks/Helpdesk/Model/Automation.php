<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

use Symfony\Component\Config\Definition\Exception\Exception;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Aheadworks\Helpdesk\Model\Ticket\ExternalKeyEncryptor;

/**
 * Class Automation
 * @package Aheadworks\Helpdesk\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Automation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Ticket repository
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * Current ticket model
     * @var null|\Aheadworks\Helpdesk\Api\Data\TicketInterface
     */
    private $currentTicketModel = null;

    /**
     * Recurring factory
     * @var Automation\RecurringFactory
     */
    private $recurringFactory;

    /**
     * Recurring resource
     * @var ResourceModel\Automation\Recurring
     */
    private $recurringResource;

    /**
     * Reccuring collection factory
     * @var ResourceModel\Automation\Recurring\CollectionFactory
     */
    private $recurringCollectionFactory;

    /**
     * Ticket collection factory
     * @var ResourceModel\Ticket\CollectionFactory
     */
    private $ticketCollectionFactory;

    /**
     * Ticket flat repository
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    private $ticketFlatRepository;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Mail sender
     * @var Mail\Sender
     */
    private $sender;

    /**
     * Agent factory
     * @var \Magento\User\Model\UserFactory
     */
    private $agentFactory;

    /**
     * Agent resource
     * @var \Magento\User\Model\ResourceModel\User
     */
    private $agentResource;

    /**
     * ThreadMessage factory
     * @var ThreadMessageFactory
     */
    private $messageFactory;

    /**
     * ThreadMessage resource
     * @var ResourceModel\ThreadMessage
     */
    private $messageResource;

    /**
     * Url builder
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Scope config
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Ticket status source
     * @var Source\Ticket\Status
     */
    private $ticketStatusSource;

    /**
     * Ticket priority source
     * @var Source\Ticket\Priority
     */
    private $ticketPrioritySource;

    /**
     * Frontend url builder
     * @var \Magento\Framework\Url
     */
    private $frontendUrlBuilder;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @var ExternalKeyEncryptor
     */
    private $externalKeyEncryptor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory
     * @param Automation\RecurringFactory $recurringFactory
     * @param ResourceModel\Automation\Recurring $recurringResource
     * @param ResourceModel\Automation\Recurring\CollectionFactory $recurringCollectionFactory
     * @param ResourceModel\ThreadMessage $threadMessageResource
     * @param ThreadMessageFactory $threadMessageFactory
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param Mail\Sender $sender
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param Source\Ticket\Status $ticketStatusSource
     * @param Source\Ticket\Priority $ticketPrioritySource
     * @param \Magento\Framework\Url $frontendUrlBuilder
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory,
        \Aheadworks\Helpdesk\Model\Automation\RecurringFactory $recurringFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring $recurringResource,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring\CollectionFactory $recurringCollectionFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        \Magento\User\Model\ResourceModel\User $userResource,
        \Magento\User\Model\UserFactory $userFactory,
        \Aheadworks\Helpdesk\Model\Mail\Sender $sender,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $ticketStatusSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $ticketPrioritySource,
        \Magento\Framework\Url $frontendUrlBuilder,
        DepartmentRepositoryInterface $departmentRepository,
        ExternalKeyEncryptor $externalKeyEncryptor,
        array $data = []
    ) {
        $this->recurringFactory = $recurringFactory;
        $this->recurringResource = $recurringResource;
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->storeManager = $storeManager;
        $this->sender = $sender;
        $this->agentFactory = $userFactory;
        $this->agentResource = $userResource;
        $this->messageFactory = $threadMessageFactory;
        $this->messageResource = $threadMessageResource;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfigInterface;
        $this->ticketStatusSource = $ticketStatusSource;
        $this->ticketPrioritySource = $ticketPrioritySource;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->departmentRepository = $departmentRepository;
        $this->externalKeyEncryptor = $externalKeyEncryptor;
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\Automation');
    }

    /**
     * Run action
     * @param $actionType
     * @param $ticketId
     * @param null $messageId
     * @return $this
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    public function runAction($actionType, $ticketId, $messageId = null)
    {
        //$actionType has view ['action' => action, 'value' => value]
        if (!isset($actionType['action']) || !isset($actionType['value'])) {
            throw new Exception(__('Action is empty'));
        }
        /** @var \Aheadworks\Helpdesk\Api\Data\TicketInterface currentTicketModel */
        $this->currentTicketModel = $this->ticketRepository->getById($ticketId);

        switch ($actionType['action']) {
            case \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_CUSTOMER_EMAIL_VALUE:
                $this->sendEmailToCustomer($actionType['value'], $messageId);
                break;
            case \Aheadworks\Helpdesk\Model\Source\Automation\Action::SEND_AGENT_EMAIL_VALUE:
                $this->sendEmailToAgent($actionType['value'], $messageId);
                break;
            case \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_STATUS_VALUE:
                $this->changeStatus($actionType['value']);
                break;
            case \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_PRIORITY_VALUE:
                $this->changePriority($actionType['value']);
                break;
            case \Aheadworks\Helpdesk\Model\Source\Automation\Action::ASSIGN_TICKET_VALUE:
                $this->assignTicket($actionType['value']);
                break;
            case \Aheadworks\Helpdesk\Model\Source\Automation\Action::CHANGE_DEPARTMENT_VALUE:
                $this->changeDepartment($actionType['value']);
                break;
        }
        return $this;
    }

    /**
     * Create recurring action
     * @param $actionType
     * @param $ticketId
     * @return $this
     */
    public function scheduleAction($actionType, $ticketId)
    {
        $recurringCollection = $this->recurringCollectionFactory->create();
        $recurringCollection
            ->addTicketFilter($ticketId)
            ->addActionFilter($actionType['action'])
            ->addNotFinishedFilter()
            ->load()
        ;

        if ($recurringCollection->getSize() === 0) {
            $recurringModel = $this->recurringFactory->create();
            $recurringModel
                ->setActionType($actionType['action'])
                ->setAction($actionType)
                ->setAutomationId($this->getId())
                ->setTicketId($ticketId)
                ->setStatus(\Aheadworks\Helpdesk\Model\Automation\Recurring::PENDING_STATUS)
            ;
            $this->recurringResource->save($recurringModel);
        }
        return $this;
    }

    /**
     * Validation automation for ticket
     * @param $ticketId
     * @return bool
     */
    public function validateForTicket($ticketId)
    {
        $result = false;
        $validateIds = $this->getValidatedTicketIds();
        if (false !== array_search($ticketId, $validateIds)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Get all validated ticket ids
     * @return array
     */
    public function getValidatedTicketIds()
    {
        /** @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Collection $ticketCollection */
        $ticketCollection = $this->ticketCollectionFactory->create();
        $ticketCollection->prepareForAutomation();

        //$condition has view ['object' => object, 'operator' => operator, 'value' => value]
        foreach ($this->getConditions() as $condition) {
            $condValue = $condition['value'];
            if ($condition['operator'] == \Aheadworks\Helpdesk\Model\Source\Automation\Operator::LIKE_VALUE) {
                $values = explode(',', $condition['value']);
                $likeQuery = [];
                foreach ($values as $value) {
                    $likeQuery[] = $ticketCollection->getConnection()->prepareSqlCondition(
                        $condition['object'],
                        [$condition['operator'] => '%'.trim($value).'%']
                    );
                }
                $likeQuery = '(' . implode(' OR ', $likeQuery) . ')';
                $ticketCollection->getSelect()->where($likeQuery);
                continue;
            }
            if (
                $condition['object'] == \Aheadworks\Helpdesk\Model\Source\Automation\Condition::CUSTOMER_GROUP_VALUE
                && false !== array_search('0', $condition['value'])
            ) {

                $groupQuery = [];
                $groupQuery[] = $ticketCollection->getConnection()->prepareSqlCondition(
                    $condition['object'],
                    [$condition['operator'] => $condition['value']]
                );
                //Query for guest group
                $groupQuery[] = $ticketCollection->getConnection()->prepareSqlCondition(
                    'main_table.customer_id',
                    ['null' => true]
                );
                $groupQuery = '(' . implode(' OR ', $groupQuery) . ')';
                $ticketCollection->getSelect()->where($groupQuery);
                continue;
            }
            if (
                $condition['object'] == \Aheadworks\Helpdesk\Model\Source\Automation\Condition::LAST_REPLIED_HOURS_VALUE
            ) {
                $currentDate = new \DateTime();
                $query = $ticketCollection->getConnection()->prepareSqlCondition(
                    "(UNIX_TIMESTAMP('{$currentDate->format('Y-m-d H:i:s')}') - UNIX_TIMESTAMP({$condition['object']}))/60/60",
                    [$condition['operator'] => $condition['value']]
                );
                $ticketCollection->getSelect()->where($query);
                continue;
            }
            $query = $ticketCollection->getConnection()->prepareSqlCondition(
                $condition['object'],
                [$condition['operator'] => $condValue]
            );
            $ticketCollection->getSelect()->where($query);
        }
        return $ticketCollection->getAllIds();
    }

    /**
     * Send email to customer action
     * @param $template
     * @param int|null $messageId
     * @return $this
     */
    private function sendEmailToCustomer($template, $messageId = null)
    {
        $customerEmail = $this->currentTicketModel->getCustomerEmail();
        $customerName = $this->currentTicketModel->getCustomerName();

        $storeId = $this->currentTicketModel->getStoreId();
        $store = $this->storeManager->getStore($storeId);
        $message = new \Magento\Framework\DataObject();

        $departmentId = $this->currentTicketModel->getDepartmentId();
        try {
            /** @var DepartmentInterface $department */
            $department = $this->departmentRepository->getById($departmentId);
            /** @var DepartmentGatewayInterface $gateway */
            $gateway = $department->getGateway();
        } catch (\Exception $e) {
            $gateway = null;
        }

        if (!$gateway) {
            /** @var DepartmentInterface $defaultDepartment */
            $defaultDepartment = $this->departmentRepository->getDefaultByWebsiteId($store->getWebsiteId());
            /** @var DepartmentGatewayInterface $gateway */
            $gateway = $defaultDepartment->getGateway();
        }

        if ($gateway) {
            $gatewayEmail = $gateway->getEmail();
            $sender = ['email' => $gatewayEmail, 'name' => $store->getFrontendName()];
        } else {
            $gatewayEmail = null;
        }

        $storeEmail = $this->scopeConfig->getValue(
            'trans_email/ident_support/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $storeName = $this->scopeConfig->getValue(
            'trans_email/ident_support/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$gatewayEmail) {
            $sender = ['email' => $storeEmail, 'name' => $storeName];
        }

        try {
            $flatModel = $this->ticketFlatRepository->getByTicketId($this->currentTicketModel->getId());
        } catch (\Exception $e) {
            $flatModel = null;
        }

        if ($flatModel) {
            $this->currentTicketModel->setData('first_message', $flatModel->getFirstMessageContent());
            $this->currentTicketModel->setData('agent_name', $flatModel->getAgentName());
            $this->currentTicketModel->setData('order_increment', $flatModel->getOrderIncrementId());
        }

        $statusLabel = $this->ticketStatusSource->getOptionLabelByValue($this->currentTicketModel->getStatus());
        $this->currentTicketModel->setData('status_label', $statusLabel);

        $priorityLabel = $this->ticketPrioritySource->getOptionLabelByValue($this->currentTicketModel->getPriority());
        $this->currentTicketModel->setData('priority_label', $priorityLabel);

        if ($messageId) {
            $message = $this->messageFactory->create();
            $this->messageResource->load($message, $messageId);
            if ($message->getType() == \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_VALUE) {
                $sender = ['email' => $message->getAuthorEmail(), 'name' => $message->getAuthorName()];
            }
        }
        $this->frontendUrlBuilder->setScope($store);
        $externalUrl = $this->frontendUrlBuilder->getUrl(
            'aw_helpdesk/ticket/external',
            [
                'key' => $this->externalKeyEncryptor->encrypt(
                    $this->currentTicketModel->getCustomerEmail(),
                    $this->currentTicketModel->getId()
                ),
                '_secure' => $store->isUrlSecure()
            ]
        );
        $emailData = [
            'template_id' => $template,
            'store_id' => $storeId,
            'from' => $sender,
            'to' => $customerEmail,
            'sender_name' => $customerName,
            'gateway' => $gatewayEmail,
            'ticket' => new \Magento\Framework\DataObject($this->currentTicketModel->toArray()),
            'author_name' => $message->getAuthorName(),
            'message' => $message,
            'store' => $store,
            'external_url' => $externalUrl
        ];

        if ($this->currentTicketModel->getCcRecipients()) {
            $emailData['cc_recipients'] = $this->currentTicketModel->getCcRecipients();
        }
        $this->sender->sendEmail($emailData);

        return $this;
    }

    /**
     * Send email to agent
     * @param $template
     * @param int|null $messageId
     * @return $this
     */
    private function sendEmailToAgent($template, $messageId = null)
    {
        if (!$this->currentTicketModel->getAgentId()) {
            return $this;
        }
        $agent = $this->agentFactory->create();
        $this->agentResource->load($agent, $this->currentTicketModel->getAgentId());
        if (!$agent || !$agent->getId()) {
            return $this;
        }
        $storeId = $this->currentTicketModel->getStoreId();
        $store = $this->storeManager->getStore($storeId);

        $departmentId = $this->currentTicketModel->getDepartmentId();
        try {
            /** @var DepartmentInterface $department */
            $department = $this->departmentRepository->getById($departmentId);
            $departmentName = $department->getName();
        } catch (\Exception $e) {
            $departmentName = '';
        }

        $message = new \Magento\Framework\DataObject();
        if ($messageId) {
            $message = $this->messageFactory->create();
            $this->messageResource->load($message, $messageId);
        }

        try {
            $flatModel = $this->ticketFlatRepository->getByTicketId($this->currentTicketModel->getId());
        } catch (\Exception $e) {
            $flatModel = null;
        }

        if ($flatModel) {
            $this->currentTicketModel->setData('first_message', $flatModel->getFirstMessageContent());
            $this->currentTicketModel->setData('agent_name', $flatModel->getAgentName());
            $this->currentTicketModel->setData('order_increment', $flatModel->getOrderIncrementId());
        }

        $statusLabel = $this->ticketStatusSource->getOptionLabelByValue($this->currentTicketModel->getStatus());
        $this->currentTicketModel->setData('status_label', $statusLabel);

        $priorityLabel = $this->ticketPrioritySource->getOptionLabelByValue($this->currentTicketModel->getPriority());
        $this->currentTicketModel->setData('priority_label', $priorityLabel);

        $storeEmail = $this->scopeConfig->getValue(
            'trans_email/ident_support/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $storeName = $this->scopeConfig->getValue(
            'trans_email/ident_support/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $backendUrl = $this->urlBuilder->getUrl(
            'aw_helpdesk/ticket/edit',
            ['id' => $this->currentTicketModel->getId()]
        );
        $this->currentTicketModel->setData('backend_url', $backendUrl);
        $emailData = [
            'template_id' => $template,
            'store_id' => $storeId,
            'from' => ['email' => $storeEmail, 'name' => $storeName],
            'to' => $agent->getEmail(),
            'sender_name' => $agent->getUsername(),
            'ticket' => new \Magento\Framework\DataObject($this->currentTicketModel->toArray()),
            'author_name' => $message->getAuthorName(),
            'agent' => $agent,
            'message' => $message,
            'store' => $store,
            'department_name' => $departmentName
        ];
        $this->sender->sendEmail($emailData, false);
        return $this;
    }

    /**
     * Change status action
     * @param string $statusId
     * @return $this
     */
    private function changeStatus($statusId)
    {
        $this->currentTicketModel->setStatus($statusId);
        $this->currentTicketModel = $this->ticketRepository->save($this->currentTicketModel);

        return $this;
    }

    /**
     * Change priority action
     * @param string $priorityId
     * @return $this
     */
    private function changePriority($priorityId)
    {
        $this->currentTicketModel->setPriority($priorityId);
        $this->currentTicketModel = $this->ticketRepository->save($this->currentTicketModel);

        return $this;
    }

    /**
     * Assign ticket action
     * @param int $agentId
     * @return $this
     */
    private function assignTicket($agentId)
    {
        $this->currentTicketModel->setAgentId((string)$agentId);
        $this->currentTicketModel = $this->ticketRepository->save($this->currentTicketModel);
        try {
            $ticketFlat = $this->ticketFlatRepository->getByTicketId($this->currentTicketModel->getId());
        } catch (\Exception $e) {
            $ticketFlat = null;
        }
        if ($ticketFlat) {
            $ticketFlat->setAgentId((string)$agentId);
            $this->ticketFlatRepository->save($ticketFlat);
        }
        return $this;
    }

    /**
     * Assign ticket action
     * @param int $departmentId
     * @return $this
     */
    private function changeDepartment($departmentId)
    {
        $this->currentTicketModel->setDepartmentId($departmentId);
        $this->currentTicketModel = $this->ticketRepository->save($this->currentTicketModel);

        return $this;
    }
}
