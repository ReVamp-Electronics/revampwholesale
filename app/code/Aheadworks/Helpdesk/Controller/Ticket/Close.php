<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Aheadworks\Helpdesk\Model\Ticket\ExternalKeyEncryptor;
use Aheadworks\Helpdesk\Model\TicketFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Ticket as TicketResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\ForwardFactory;
use Aheadworks\Helpdesk\Model\Source\Ticket\Status;
use Aheadworks\Helpdesk\Api\TicketRepositoryInterface;

/**
 * Close ticket action
 * @package Aheadworks\Helpdesk\Controller\Ticket
 */
class Close extends \Aheadworks\Helpdesk\Controller\Ticket
{
    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param TicketFactory $ticketModelFactory
     * @param TicketResource $ticketResource
     * @param TicketRepositoryInterface $ticketRepository
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ExternalKeyEncryptor $externalKeyEncryptor,
        TicketFactory $ticketModelFactory,
        TicketResource $ticketResource,
        TicketRepositoryInterface $ticketRepository,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->ticketRepository = $ticketRepository;
        parent::__construct($context, $customerSession, $externalKeyEncryptor, $ticketModelFactory, $ticketResource);
    }

    /**
     * Close ticket
     *
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ticketModel = $this->getTicket();
        $externalKey = $this->getRequest()->getParam('key');
        try {
            if ($ticketModel) {
                /** @var \Aheadworks\Helpdesk\Api\Data\TicketInterface $ticket */
                $ticket = $this->ticketRepository->getById($ticketModel->getId());
                if (
                    $ticket->getId()
                    && (
                        $this->isCustomerValid($ticket->getCustomerId(), $ticket->getCustomerEmail())
                        || $this->isExternalKeyValid($externalKey, $ticket->getId())
                    )
                ) {
                    $ticket->setStatus(Status::SOLVED_VALUE);
                    $this->ticketRepository->save($ticket);

                    $this->messageManager->addSuccessMessage(__('Ticket was closed.'));
                    if ($this->customerSession->authenticate()) {
                        $path = '*/*/view';
                        $resultRedirect->setPath($path, ['id' => $ticket->getId()]);
                    } else {
                        $path = '*/*/external';
                        $resultRedirect->setPath($path, ['key' => $externalKey]);
                    }
                    return $resultRedirect;
                }
            }
            $this->messageManager->addErrorMessage(__('Wrong ticket ID'));
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while closing the ticket.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
