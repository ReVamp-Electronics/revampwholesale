<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\ForwardFactory;
use Aheadworks\Helpdesk\Model\Source\Ticket\Priority;
use Aheadworks\Helpdesk\Model\Source\Ticket\Status;

/**
 * Save ticket action
 * @package Aheadworks\Helpdesk\Controller\Ticket
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Forward factory
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * Form key validator
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    private $ticketFlatRepository;

    /**
     * Ticket data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory
     */
    private $ticketDataFactory;

    /**
     * Ticket flat data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory
     */
    private $ticketFlatDataFactory;

    /**
     * Data object helper
     *
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Thread message factory
     *
     * @var \Aheadworks\Helpdesk\Model\ThreadMessageFactory
     */
    private $threadMessageFactory;

    /**
     * Thread message resource
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage
     */
    private $threadMessageResource;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource
     * @param \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory
     * @param ForwardFactory $resultForwardFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory,
        \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->customerSession = $customerSession;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->ticketDataFactory = $ticketInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->ticketFlatDataFactory = $ticketFlatInterfaceFactory;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageResource = $threadMessageResource;
        parent::__construct($context);
    }

    /**
     * Save ticket action
     * @return $this
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->validateFormKey()) {
            return $resultRedirect->setPath('*/*/');
        }
        if ($data) {
            $this->customerSession->setFormData($data);
            unset($data['form_key']);
            $ticket = $this->ticketDataFactory->create();
            $ticketFlat = $this->ticketFlatDataFactory->create();

            if (isset($data['id'])) {
                unset($data['id']);
            }
            $data['customer_email'] = $this->customerSession->getCustomer()->getEmail();
            $data['priority'] = Priority::DEFAULT_VALUE;
            $data['status'] = Status::OPEN_VALUE;
            $data['agent_id'] = 0;
            $data['subject'] = strip_tags($data['subject']);
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
                $data['author_name'] = $this->customerSession->getCustomer()->getName();
                $data['author_email'] = $this->customerSession->getCustomer()->getEmail();
                if (isset($data['content']) && $data['content']) {
                    $data['content'] = strip_tags($data['content']);
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
                $this->customerSession->unsetFormData();
                return $resultRedirect->setPath('*/*/view', ['id' => $ticket->getId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the ticket.'));
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate form
     * @return bool
     */
    protected function validateFormKey()
    {
        return $this->formKeyValidator->validate($this->getRequest());
    }

    /**
     * Check customer authentication
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
