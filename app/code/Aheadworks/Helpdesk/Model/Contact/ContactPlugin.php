<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Contact;

use Aheadworks\Helpdesk\Model\Source\Ticket\Priority;
use Aheadworks\Helpdesk\Model\Source\Ticket\Status;

/**
 * Class ContactPlugin
 * @package Aheadworks\Helpdesk\Model\Contact
 */
class ContactPlugin
{
    /**
     * Redirect interface
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Message manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Ticket repository
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * Ticket flat repository
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    protected $ticketFlatRepository;

    /**
     * Ticket data factory
     * @var \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory
     */
    protected $ticketDataFactory;

    /**
     * Data object helper
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Ticket flat data factory
     * @var \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory
     */
    protected $ticketFlatDataFactory;

    /**
     * Thread message factory
     * @var \Aheadworks\Helpdesk\Model\ThreadMessageFactory
     */
    protected $threadMessageFactory;

    /**
     * Thread message resource
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage
     */
    protected $threadMessageResource;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource
     * @param \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory,
        \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->storeManager = $storeManager;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->ticketDataFactory = $ticketInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->ticketFlatDataFactory = $ticketFlatInterfaceFactory;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageResource = $threadMessageResource;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
    }

    /**
     * Rewrite native contact us controller and create ticket via contact us form
     * @param \Magento\Contact\Controller\Index\Post $subject
     * @param callable $proceed
     * @return $this
     */
    public function aroundExecute(\Magento\Contact\Controller\Index\Post $subject, \Closure $proceed)
    {
        $request = $subject->getRequest();
        $ticket = $this->ticketDataFactory->create();
        $ticketFlat = $this->ticketFlatDataFactory->create();
        $data = $request->getPostValue();
        $data['customer_email'] = strip_tags($data['email']);
        $data['customer_name'] = strip_tags($data['name']);
        $data['priority'] = Priority::DEFAULT_VALUE;
        $data['status'] = Status::OPEN_VALUE;
        $data['agent_id'] = 0;
        $data['subject'] = __('%1 <%2> via "Contact Us"', trim($data['customer_name']), trim($data['customer_email']));

        $this->dataObjectHelper->populateWithArray(
            $ticket,
            $data,
            '\Aheadworks\Helpdesk\Api\Data\TicketInterface'
        );
        try {
            $ticket = $this->ticketRepository->save($ticket);
            //save message
            $data['ticket_id'] = $ticket->getId();
            $data['type'] = \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE;
            $data['author_name'] = $data['customer_name'];
            $data['author_email'] = $data['customer_email'];
            $data['content'] = strip_tags($data['comment']);
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
            $this->messageManager->addSuccess(__('Ticket was successfully created.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the ticket.'));
        }
        $this->redirect->redirect($subject->getResponse(), 'contact/index', []);
        return;
    }
}