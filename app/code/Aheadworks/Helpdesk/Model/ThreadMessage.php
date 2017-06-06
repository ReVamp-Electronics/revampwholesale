<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

/**
 * Class ThreadMessage
 * @package Aheadworks\Helpdesk\Model
 */
class ThreadMessage extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Reply types
     */
    const OWNER_ADMIN_VALUE = 'admin-reply';
    const OWNER_ADMIN_INTERNAL_VALUE = 'admin-internal';
    const OWNER_CUSTOMER_VALUE = 'customer-reply';
    const OWNER_SYSTEM_VALUE = 'system-message';

    /**
     * Automation resource model
     * @var ResourceModel\Automation
     */
    protected $automationResource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\TicketFlat $resource
     * @param ResourceModel\Ticket\Grid\Collection $resourceCollection
     * @param ResourceModel\Automation $automationResource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $resource = null,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection $resourceCollection = null,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource,
        array $data = []
    ) {
        $this->automationResource = $automationResource;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage');
    }

    /**
     * If reply from admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getType() == self::OWNER_ADMIN_VALUE;
    }

    /**
     * If reply from customer
     *
     * @return bool
     */
    public function isCustomer()
    {
        return $this->getType() == self::OWNER_CUSTOMER_VALUE;
    }

    /**
     * If reply from system
     *
     * @return bool
     */
    public function isSystem()
    {
        return $this->getType() == self::OWNER_SYSTEM_VALUE;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthorName()
    {
        if ($this->getId() && !$this->hasData('author_name')) {
            $this->getResource()->attachAuthor($this);
        }
        return $this->getData('author_name');
    }

    /**
     * Get attachments
     *
     * @param null $index
     * @return mixed
     */
    public function getAttachments($index = null)
    {
        if ($this->getId() && !$this->hasData('attachments')) {
            $this->getResource()->attachAttachmentsData($this);
        }
        return $this->getData('attachments', $index);
    }

    /**
     * After save. Check automation
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isObjectNew()) {
            switch ($this->getType()) {
                case 'customer-reply':
                    $this
                        ->automationResource
                        ->createAutomationAction(
                            \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_CUSTOMER_REPLY_VALUE,
                            $this->getTicketId(),
                            $this->getId()
                        );
                    break;
                case 'admin-reply':
                    $this
                        ->automationResource
                        ->createAutomationAction(
                            \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_AGENT_REPLY_VALUE,
                            $this->getTicketId(),
                            $this->getId()
                        );
                    break;
            }
        }
        parent::afterSave();
        return $this;
    }
}