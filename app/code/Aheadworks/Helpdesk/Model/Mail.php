<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model;

/**
 * Class Mail
 * @package Aheadworks\Helpdesk\Model
 */
class Mail extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Mail types constant
     */
    const TYPE_FROM_GATEWAY   = 1;
    const TYPE_FROM_STORE = 2;

    /**
     * Mail statuses constant
     */
    const STATUS_UNPROCESSED   = 1;
    const STATUS_PROCESSED = 2;

    /**
     * Attachment factory
     * @var \Aheadworks\Helpdesk\Model\Mail\AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * Attachment resource
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment
     */
    protected $attachmentResource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Mail $resource
     * @param ResourceModel\Mail\Collection $resourceCollection
     * @param Mail\AttachmentFactory $attachmentFactory
     * @param ResourceModel\Mail\Attachment $attachmentResource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail $resource = null,
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Collection $resourceCollection = null,
        \Aheadworks\Helpdesk\Model\Mail\AttachmentFactory $attachmentFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment $attachmentResource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResource = $attachmentResource;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\Mail');
    }

    /**
     * Add attachments from array
     *
     * @param $data
     * @return $this
     */
    public function addAttachmentFromArray($data)
    {
        if (!array_key_exists('filename', $data) || !array_key_exists('filename', $data)) {
            return $this;
        }

        $attachment = $this->attachmentFactory->create();
        $attachment
            ->setName($data['filename'])
            ->setContent($data['content'])
            ->setMailboxId($this->getId())
        ;
        $this->attachmentResource->save($attachment);

        return $this;
    }
}