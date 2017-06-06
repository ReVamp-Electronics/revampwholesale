<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Customer\Ticket;

/**
 * Class Thread
 * @package Aheadworks\Helpdesk\Block\Customer\Ticket
 */
class Thread extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * File helper
     * @var \Aheadworks\Helpdesk\Helper\File
     */
    protected $fileHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aheadworks\Helpdesk\Helper\File $fileHelper
     * @param \Aheadworks\Helpdesk\Helper\SmartDate $smartDateHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aheadworks\Helpdesk\Helper\File $fileHelper,
        \Aheadworks\Helpdesk\Helper\SmartDate $smartDateHelper,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->fileHelper = $fileHelper;
        $this->smartDateHelper = $smartDateHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get current ticket
     * @return \Aheadworks\Helpdesk\Model\Ticket
     */
    public function getTicket()
    {
        return $this->coreRegistry->registry('aw_helpdesk_ticket');
    }

    /**
     * Get current external key
     * @return string
     */
    public function getExternalKey()
    {
        return $this->coreRegistry->registry('aw_helpdesk_key');
    }

    /**
     * Get submit url
     * @return string
     */
    public function getSubmitReplyUrl()
    {
        return $this->getUrl('aw_helpdesk/ticket/reply', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get download url
     *
     * @param int $attachmentId
     * @return string
     */
    public function getDownloadUrl($attachmentId)
    {
        if ($this->getExternalKey()) {
            return $this->getUrl(
                '*/*/download',
                [
                    'attachment_id' => $attachmentId,
                    'key' => $this->getExternalKey(),
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        }
        return $this->getUrl(
            '*/*/download',
            ['attachment_id' => $attachmentId, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Format file size
     *
     * @param int $size
     * @return string
     */
    public function formatFileSize($size)
    {
        return $this->fileHelper->getTextFileSize($size);
    }

    /**
     * Get create ticket url
     * @return string
     */
    public function getCreateTicketUrl()
    {
        if ($this->getExternalKey()) {
            return null;
        }
        return $this->getUrl(
            'aw_helpdesk/ticket',
            ['_secure' => $this->getRequest()->isSecure()]
        ) . "#create_ticket_form";
    }

    /**
     * Format smart date
     * @param mixed $date
     * @return string
     */
    public function getSmartDate($date)
    {
        return $this->smartDateHelper->getSmartDate($date);
    }
}
