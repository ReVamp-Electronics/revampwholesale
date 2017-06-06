<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Download
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class Download extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Attachment model factory
     * @var \Aheadworks\Helpdesk\Model\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * File factory
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Aheadworks\Helpdesk\Model\AttachmentFactory $attachmentFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Aheadworks\Helpdesk\Model\AttachmentFactory $attachmentFactory
    ) {
        $this->attachmentFactory = $attachmentFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Download action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $attachmentId = $this->getRequest()->getParam('attachment_id');
        $attachment = $this->attachmentFactory->create();
        if (!$attachmentId || !$attachment->load($attachmentId)->getId()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addError(__('File not found'));
            return $resultRedirect->setPath('*/*');
        }

        $this->fileFactory->create(
            $attachment->getName(),
            $attachment->getContent(),
            DirectoryList::MEDIA,
            'application/octet-stream',
            $attachment->getContentLength()
        );
    }
}