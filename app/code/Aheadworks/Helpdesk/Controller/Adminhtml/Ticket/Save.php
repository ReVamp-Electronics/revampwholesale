<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Aheadworks\Helpdesk\Model\ResourceModel\Ticket;
use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;
use Magento\Backend\App\Action;
use Magento\Framework\Message\Error;

/**
 * Class Save
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class Save extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    protected $ticketFlatRepository;

    /**
     * Ticket data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory
     */
    protected $ticketDataFactory;

    /**
     * Ticket flat data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory
     */
    protected $ticketFlatDataFactory;

    /**
     * Data object helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Thread message factory
     *
     * @var \Aheadworks\Helpdesk\Model\ThreadMessageFactory
     */
    protected $threadMessageFactory;

    /**
     * Thread message resource
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage
     */
    protected $threadMessageResource;

    /**
     * @var PermissionValidator
     */
    private $permissionValidator;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource
     * @param \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory
     * @param PermissionValidator $permissionValidator
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory,
        \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        PermissionValidator $permissionValidator
    ) {
        parent::__construct($context, $resultPageFactory);

        $this->ticketDataFactory = $ticketInterfaceFactory;
        $this->ticketFlatDataFactory = $ticketFlatInterfaceFactory;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->authSession = $authSession;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageResource = $threadMessageResource;
        $this->permissionValidator = $permissionValidator;
    }

    /**
     * Save action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $ticket = $this->ticketDataFactory->create();
            $ticketFlat = $this->ticketFlatDataFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $ticket,
                $data,
                \Aheadworks\Helpdesk\Api\Data\TicketInterface::class
            );

            if (!$this->permissionValidator->updateValidate($ticket)) {
                $this->messageManager->addErrorMessage(__('Not enough permissions to save the ticket.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $data['ticket_id']]);
            }

            try {
                $ticket = $this->ticketRepository->save($ticket);
                //save messages
                $data['ticket_id'] = $ticket->getId();
                $data['type'] = \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_VALUE;
                $data['author_name'] = $this->authSession->getUser()->getName();
                $data['author_email'] = $this->authSession->getUser()->getEmail();
                if (isset($data['content']) && $data['content']) {
                    $threadMessage = $this->threadMessageFactory->create()
                        ->setData($data)
                    ;
                    $this->threadMessageResource->save($threadMessage);
                }

                $ticketFlat->setData('order_id', $ticket->getOrderId());
                $ticketFlat->setData('agent_id', $ticket->getAgentId());
                $ticketFlat->setTicketId($ticket->getId());

                $this->ticketFlatRepository->save($ticketFlat);

                $this->messageManager->addSuccessMessage(__('Ticket was successfully created.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->addSessionErrorMessages($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the ticket.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Add error messages
     *
     * @param $messages
     */
    protected function addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }
}
