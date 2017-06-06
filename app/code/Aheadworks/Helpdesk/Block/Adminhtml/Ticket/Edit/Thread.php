<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit;

use \Aheadworks\Helpdesk\Model\TicketManager;

/**
 * Class Thread
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit
 */
class Thread extends \Magento\Backend\Block\Template
{
    /**
     * Session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * File helper
     *
     * @var \Aheadworks\Helpdesk\Helper\File
     */
    protected $fileHelper;

    /**
     * ThreadMessage collection
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection
     */
    protected $threadMessageCollection;

    /**
     * Thread template
     *
     * @var string
     */
    protected $_template = 'ticket/edit/thread.phtml';

    /**
     * Statuses source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $statusSource;

    /**
     * Date format helper
     * @var \Aheadworks\Helpdesk\Helper\SmartDate
     */
    protected $smartDateHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Aheadworks\Helpdesk\Helper\File $fileHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param \Aheadworks\Helpdesk\Helper\SmartDate $smartDate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Aheadworks\Helpdesk\Helper\File $fileHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        \Aheadworks\Helpdesk\Helper\SmartDate $smartDate,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->fileHelper = $fileHelper;
        $this->coreRegistry = $registry;
        $this->statusSource = $statusSource;
        $this->smartDateHelper = $smartDate;
        parent::__construct($context, $data);
    }

    /**
     * Get ticket model
     *
     * @return \Aheadworks\Helpdesk\Model\Ticket|null
     */
    public function getTicketModel()
    {
        return $this->coreRegistry->registry('aw_helpdesk_ticket');
    }

    /**
     * Get ThreadMessage collection
     *
     * @return \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection|null
     */
    public function getThreadMessageCollection()
    {
        if ($this->threadMessageCollection === null) {
            $this->threadMessageCollection = $this->getTicketModel()->getThread();
        }
        return $this->threadMessageCollection;
    }

    /**
     * Retrieve formatted owner name, owner info and message created date
     *
     * @param \Aheadworks\Helpdesk\Model\ThreadMessage $threadMessage
     * @return string
     */
    public function getMessageHeader($threadMessage)
    {
        if ($threadMessage->getIsSystem()) {
            $ownerName = __("System");
        } else {
            $ownerName = $threadMessage->getAuthorName();
        }

        $dateCreated = $this->smartDateHelper->getSmartDate($threadMessage->getCreatedAt());
        return "<span class='name'>{$ownerName}</span><span class='date'>{$dateCreated}</span>";
    }

    /**
     * Get submit reply url
     *
     * @return string
     */
    public function getSubmitReplyUrl()
    {
        return $this->getUrl('*/*/reply');
    }

    /**
     * Get file upload url
     *
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('*/*/upload');
    }

    /**
     * Get download url
     *
     * @param $attachmentId
     * @return string
     */
    public function getDownloadUrl($attachmentId)
    {
        return $this->getUrl('*/*/download', ['attachment_id' => $attachmentId]);
    }

    /**
     * Format file size
     *
     * @param $size
     * @return string
     */
    public function formatFileSize($size)
    {
        return $this->fileHelper->getTextFileSize($size);
    }

    /**
     * Get default ticket status
     *
     * @return string
     */
    public function getDefaultStatus()
    {
        return \Aheadworks\Helpdesk\Model\Source\Ticket\Status::DEFAULT_STATUS;
    }

    /**
     * Get all ticket statuses as json
     *
     * @return string
     */
    public function getButtonStatusLabelsAsJson()
    {
        return json_encode($this->statusSource->getOptionArray());
    }

    /**
     * Get button type as json
     *
     * @return string
     */
    public function getButtonTypeAsJson()
    {
        $result = ['save' => __('Save'), 'submit' => __('Submit')];
        return json_encode($result);
    }
}