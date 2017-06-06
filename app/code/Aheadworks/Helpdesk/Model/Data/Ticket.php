<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Data;

use \Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Ticket
 * @package Aheadworks\Helpdesk\Model\Data
 */
class Ticket extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Aheadworks\Helpdesk\Api\Data\TicketInterface
{
    /**
     * Status source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $statusSource;

    /**
     * Priority source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Priority
     */
    protected $prioritySource;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        $data = []
    ) {
        $this->statusSource = $statusSource;
        $this->prioritySource = $prioritySource;
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
    }

    /**
     * Get ticket id
     *
     * @api
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set ticket id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get uid
     *
     * @api
     * @return string
     */
    public function getUid()
    {
        return $this->_get(self::UID);
    }

    /**
     * Set uid
     *
     * @api
     * @param string $uid
     * @return $this
     */
    public function setUid($uid)
    {
        return $this->setData(self::UID, $uid);
    }

    /**
     * Get department id
     *
     * @api
     * @return int
     */
    public function getDepartmentId()
    {
        return $this->_get(self::DEPARTMENT_ID);
    }

    /**
     * Set department id
     *
     * @api
     * @param int $departmentId
     * @return $this
     */
    public function setDepartmentId($departmentId)
    {
        return $this->setData(self::DEPARTMENT_ID, $departmentId);
    }

    /**
     * Get created at
     *
     * @api
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @api
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get customer id
     *
     * @api
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer id
     *
     * @api
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get customer email
     *
     * @api
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_get(self::CUSTOMER_EMAIL);
    }

    /**
     * Set customer email
     *
     * @api
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Get customer name
     *
     * @api
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->_get(self::CUSTOMER_NAME);
    }

    /**
     * Set customer name
     *
     * @api
     * @param string $customerName
     * @return $this
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * Get ticket subject
     *
     * @api
     * @return string
     */
    public function getSubject()
    {
        return $this->_get(self::SUBJECT);
    }

    /**
     * Set ticket subject
     *
     * @api
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * Get ticket status
     *
     * @api
     * @return string
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set ticket status
     *
     * @api
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get ticket priority
     *
     * @api
     * @return string
     */
    public function getPriority()
    {
        return $this->_get(self::PRIORITY);
    }

    /**
     * Set ticket priority
     *
     * @api
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Get agent id
     *
     * @api
     * @return int|null
     */
    public function getAgentId()
    {
        return $this->_get(self::AGENT_ID);
    }

    /**
     * Set agent id
     *
     * @api
     * @param int $agentId
     * @return $this
     */
    public function setAgentId($agentId)
    {
        return $this->setData(self::AGENT_ID, $agentId);
    }

    /**
     * Get order id
     *
     * @api
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order id
     *
     * @api
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get CC Recipients
     *
     * @api
     * @return string|null
     */
    public function getCcRecipients()
    {
        return $this->_get(self::CC_RECIPIENTS);
    }

    /**
     * Set CC Recipients
     *
     * @api
     * @param string $ccRecipients
     * @return $this
     */
    public function setCcRecipients($ccRecipients)
    {
        return $this->setData(self::CC_RECIPIENTS, $ccRecipients);
    }

    /**
     * Get store id
     *
     * @api
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Set store id
     *
     * @api
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->statusSource->getOptionLabelByValue($this->getStatus());
    }

    /**
     * Get priority label
     *
     * @return string
     */
    public function getPriorityLabel()
    {
        return $this->prioritySource->getOptionLabelByValue($this->getPriority());
    }

    /**
     * @param mixed $key
     * @return mixed|null
     */
    public function getData($key)
    {
        return $this->_get($key);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->__toArray();
    }
}
