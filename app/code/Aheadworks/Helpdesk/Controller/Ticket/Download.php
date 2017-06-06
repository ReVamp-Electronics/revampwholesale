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
use Magento\Framework\App\Filesystem\DirectoryList;
use Aheadworks\Helpdesk\Model\AttachmentFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Attachment as AttachmentResource;
use Aheadworks\Helpdesk\Model\ThreadMessageFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage as ThreadMessageResource;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class Download
 * @package Aheadworks\Helpdesk\Controller\Ticket
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Download extends \Aheadworks\Helpdesk\Controller\Ticket
{
    /**
     * @var AttachmentFactory
     */
    private $attachmentModelFactory;

    /**
     * @var AttachmentResource
     */
    private $attachmentResourceModel;

    /**
     * @var ThreadMessageFactory
     */
    private $messageModelFactory;

    /**
     * @var ThreadMessageResource
     */
    private $messageResourceModel;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param TicketFactory $ticketModelFactory
     * @param TicketResource $ticketResource
     * @param AttachmentFactory $attachmentModelFactory
     * @param AttachmentResource $attachmentResource
     * @param ThreadMessageFactory $messageModelFactory
     * @param ThreadMessageResource $messageResource
     * @param FileFactory $fileFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ExternalKeyEncryptor $externalKeyEncryptor,
        TicketFactory $ticketModelFactory,
        TicketResource $ticketResource,
        AttachmentFactory $attachmentModelFactory,
        AttachmentResource $attachmentResource,
        ThreadMessageFactory $messageModelFactory,
        ThreadMessageResource $messageResource,
        FileFactory $fileFactory
    ) {
        parent::__construct($context, $customerSession, $externalKeyEncryptor, $ticketModelFactory, $ticketResource);
        $this->attachmentModelFactory = $attachmentModelFactory;
        $this->attachmentResourceModel = $attachmentResource;
        $this->messageModelFactory = $messageModelFactory;
        $this->messageResourceModel = $messageResource;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Download action
     * {@inheritdoc}
     */
    public function execute()
    {
        $attachmentId = $this->getRequest()->getParam('attachment_id');
        $externalKey = $this->getRequest()->getParam('key');
        if ($attachmentId) {
            /** @var \Aheadworks\Helpdesk\Model\Attachment $attachment */
            $attachment = $this->attachmentModelFactory->create();
            $this->attachmentResourceModel->load($attachment, $attachmentId);
            if ($attachment->getId() && $attachment->getMessageId()) {
                $message = $this->messageModelFactory->create();
                $this->messageResourceModel->load($message, $attachment->getMessageId());
                if ($message->getId() && $message->getTicketId()) {
                    $ticket = $this->ticketModelFactory->create();
                    $this->ticketResource->load($ticket, $message->getTicketId());
                    if (
                        $ticket->getId()
                        && (
                            $this->isCustomerValid($ticket->getCustomerId(), $ticket->getCustomerEmail())
                            || $this->isExternalKeyValid($externalKey, $ticket->getId())
                        )
                    ) {
                        $this->fileFactory->create(
                            $attachment->getName(),
                            $attachment->getContent(),
                            DirectoryList::MEDIA,
                            'application/octet-stream',
                            $attachment->getContentLength()
                        );
                    }
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->messageManager->addErrorMessage(__('File not found'));
        return $resultRedirect->setPath('*/*');
    }
}
