<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Download extends \Aheadworks\Rma\Controller\Adminhtml\Rma
{
    /**
     * @var \Aheadworks\Rma\Model\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Aheadworks\Rma\Model\AttachmentFactory $attachmentFactory
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