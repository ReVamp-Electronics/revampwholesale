<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\JsonFactory;
use Aheadworks\Rma\Model\ThreadMessage;

/**
 * Class Download
 * @package Aheadworks\Rma\Controller\Customer
 */
class Download extends \Aheadworks\Rma\Controller\Customer
{
    /**
     * @var \Aheadworks\Rma\Model\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @var \Aheadworks\Rma\Model\ThreadMessageFactory
     */
    private $threadMessageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aheadworks\Rma\Model\RequestManager $requestManager
     * @param \Aheadworks\Rma\Model\RequestFactory $requestFactory
     * @param \Aheadworks\Rma\Model\AttachmentFactory $attachmentFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Aheadworks\Rma\Model\ThreadMessageFactory $threadMessageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aheadworks\Rma\Model\RequestManager $requestManager,
        \Aheadworks\Rma\Model\RequestFactory $requestFactory,
        \Aheadworks\Rma\Model\AttachmentFactory $attachmentFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Aheadworks\Rma\Model\ThreadMessageFactory $threadMessageFactory
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $formKeyValidator,
            $scopeConfig,
            $requestManager,
            $requestFactory,
            $customerSession
        );
        $this->attachmentFactory = $attachmentFactory;
        $this->fileFactory = $fileFactory;
        $this->threadMessageFactory = $threadMessageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $attachment = $this->getAttachment();
            if ($this->isAttachmentValid($attachment)) {
                $this->fileFactory->create(
                    $attachment->getName(),
                    $attachment->getContent(),
                    DirectoryList::MEDIA,
                    'application/octet-stream',
                    $attachment->getContentLength()
                );
            }
        } catch (LocalizedException $e) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('File not found'));
            return $resultRedirect->setPath('*/*');
        }
    }

    /**
     * Load attachment of RMA request message
     *
     * @return \Aheadworks\Rma\Model\Attachment|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttachment()
    {
        $attachment = null;
        if ($attachmentId = $this->getRequest()->getParam('id')) {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);
        }
        if (!$attachment || !$attachment->getId()) {
            throw new LocalizedException(__('File not found'));
        }
        return $attachment;
    }

    /**
     * Attachment validation
     *
     * @param \Aheadworks\Rma\Model\Attachment $attachment
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isAttachmentValid($attachment)
    {
        $result = false;
        if ($attachment) {
            $threadMessage = $this->threadMessageFactory->create()->load($attachment->getMessageId());
            if ($threadMessage && $threadMessage->getId()) {
                $rmaRequest = $this->loadRmaRequest($threadMessage->getRequestId());
                $result = $this->isRequestValid($rmaRequest);
            } else {
                throw new LocalizedException(__('Thread message not found'));
            }
        }
        return $result;
    }
}