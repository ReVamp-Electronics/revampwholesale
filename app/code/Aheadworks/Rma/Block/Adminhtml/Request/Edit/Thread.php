<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit;

use \Aheadworks\Rma\Model\RequestManager;

class Thread extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Aheadworks\Rma\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection
     */
    protected $threadMessageCollection;

    protected $_template = 'request/edit/thread.phtml';

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Aheadworks\Rma\Helper\File $fileHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Aheadworks\Rma\Helper\File $fileHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->fileHelper = $fileHelper;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Aheadworks\Rma\Model\Request|null
     */
    public function getRequestModel()
    {
        return $this->coreRegistry->registry('aw_rma_request');
    }

    /**
     * @return \Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection|null
     */
    public function getThreadMessageCollection()
    {
        if ($this->threadMessageCollection === null) {
            $this->threadMessageCollection = $this->coreRegistry->registry('aw_rma_request')->getThread();
        }
        return $this->threadMessageCollection;
    }

    /**
     * Retrieve formatted owner name, owner info and message created date
     * @param \Aheadworks\Rma\Model\ThreadMessage $threadMessage
     * @return string
     */
    public function getMessageHeader($threadMessage)
    {
        if ($threadMessage->getIsAuto()) {
            $ownerName = __("Automessage");
            $ownerInfo =  $this->_scopeConfig->getValue(
                RequestManager::XML_PATH_DEPARTMENT_NAME,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getRequestModel()->getStoreId()
            );
        } else {
            $ownerName = $threadMessage->getOwnerName();
            $ownerInfo =  $threadMessage->isAdmin() ? _("Administrator") : _("Customer");
        }
        if ($threadMessage->isAdmin() && $this->currentUserIsOwner($threadMessage)) {
            $ownerInfo .= ", me";
        }
        if (!$threadMessage->isAdmin() && !$this->getRequestModel()->getCustomerId()) {
            $ownerName = $this->getRequestModel()->getCustomerName();
        }
        $dateCreated = $this->formatDate($threadMessage->getCreatedAt(), \IntlDateFormatter::MEDIUM, true);
        return "{$ownerName} ({$ownerInfo}), {$dateCreated}";
    }

    /**
     * @param \Aheadworks\Rma\Model\ThreadMessage $threadMessage
     * @return bool
     */
    public function currentUserIsOwner($threadMessage)
    {
        return $threadMessage->getOwnerId() === $this->authSession->getUser()->getId();
    }

    /**
     * @return string
     */
    public function getSubmitReplyUrl()
    {
        return $this->getUrl('*/*/reply');
    }

    /**
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('*/*/upload');
    }

    /**
     * @param $attachmentId
     * @return string
     */
    public function getDownloadUrl($attachmentId)
    {
        return $this->getUrl('*/*/download', ['attachment_id' => $attachmentId]);
    }

    /**
     * @param $size
     * @return string
     */
    public function formatFileSize($size)
    {
        return $this->fileHelper->getTextFileSize($size);
    }
}