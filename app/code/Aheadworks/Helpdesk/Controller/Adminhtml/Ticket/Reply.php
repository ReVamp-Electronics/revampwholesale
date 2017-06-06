<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Reply
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class Reply extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    private $ticketRepository;

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
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    private $ticketFlatRepository;

    /**
     * @var PermissionValidator
     */
    private $permissionValidator;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource
     * @param \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param PermissionValidator $permissionValidator
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        PermissionValidator $permissionValidator
    ) {
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageResource = $threadMessageResource;
        $this->authSession = $authSession;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->permissionValidator = $permissionValidator;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Reply action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            if ($data['is_internal']) {
                $data['type'] = \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_INTERNAL_VALUE;
            } else {
                $data['type'] = \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_VALUE;
            }

            //update ticket
            $ticketModel = $this->ticketRepository->getById($data['ticket_id']);

            if (!$this->permissionValidator->updateValidate($ticketModel)) {
                $this->messageManager->addErrorMessage(__('Not enough permissions to update the ticket.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $data['ticket_id']]);
            }

            foreach ($data as $key => $item) {
                $ticketModel->setData($key, $item);
            }
            $this->ticketRepository->save($ticketModel);

            $data['author_name'] = $this->authSession->getUser()->getName();
            $data['author_email'] = $this->authSession->getUser()->getEmail();

            try {
                if (
                    (isset($data['content']) && $data['content'])
                    || (isset($data['attachment']) && $data['attachment'])
                ) {
                    $threadMessage = $this->threadMessageFactory->create()
                        ->setData($data)
                    ;
                    $this->threadMessageResource->save($threadMessage);
                }

                //update ticket flat
                $ticketFlat = $this->ticketFlatRepository->getByTicketId($ticketModel->getId());
                $ticketFlat->setData('order_id', $ticketModel->getOrderId());
                $ticketFlat->setData('agent_id', $ticketModel->getAgentId());
                $this->ticketFlatRepository->save($ticketFlat);
                $this->messageManager->addSuccessMessage(__('Reply successfully added.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while adding the reply.'));
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $data['ticket_id']]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}