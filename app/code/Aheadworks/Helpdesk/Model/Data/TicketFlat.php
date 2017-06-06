<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Data;

use \Magento\Framework\Api\AttributeValueFactory;

/**
 * Class TicketFlat
 * @package Aheadworks\Helpdesk\Model\Data
 */
class TicketFlat extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        $data = []
    ) {
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
    }

    /**
     * Get flat id
     *
     * @return int|null
     */
    public function getEntityId() {
        return $this->_get(self::ID);
    }

    /**
     * Set flat id
     *
     * @param int $id
     * @return $this
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get ticket id
     *
     * @return int|null
     */
    public function getTicketId() {
        return $this->_get(self::TICKET_ID);
    }

    /**
     * Set ticket id
     *
     * @param int $id
     * @return $this
     */
    public function setTicketId($id)
    {
        return $this->setData(self::TICKET_ID, $id);
    }

    /**
     * Get order increment ID
     *
     * @return mixed
     */
    public function getOrderIncrementId()
    {
        return $this->_get(self::ORDER_INCREMENT_ID);
    }

    /**
     * Set order increment ID
     *
     * @param $incrementId
     * @return mixed
     */
    public function setOrderIncrementId($incrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $incrementId);
    }

    /**
     * Get agent id
     *
     * @return mixed
     */
    public function getAgentId()
    {
        return $this->_get(self::AGENT_ID);
    }

    /**
     * Set agent id
     *
     * @param $agentId
     * @return mixed
     */
    public function setAgentId($agentId)
    {
        return $this->setData(self::AGENT_ID, $agentId);
    }

    /**
     * Get agent name
     *
     * @return mixed
     */
    public function getAgentName()
    {
        return $this->_get(self::AGENT_NAME);
    }

    /**
     * Set agent name
     *
     * @param $agentName
     * @return mixed
     */
    public function setAgentName($agentName)
    {
        return $this->setData(self::AGENT_NAME, $agentName);
    }

    /**
     * Get last reply type
     *
     * @return mixed
     */
    public function getLastReplyType()
    {
        return $this->_get(self::LAST_REPLY_TYPE);
    }

    /**
     * Set last reply type
     *
     * @param $replyType
     * @return mixed
     */
    public function setLastReplyType($replyType)
    {
        return $this->setData(self::LAST_REPLY_TYPE, $replyType);
    }

    /**
     * Get last reply by
     *
     * @return mixed
     */
    public function getLastReplyBy()
    {
        return $this->_get(self::LAST_REPLY_BY);
    }

    /**
     * Set last reply by
     *
     * @param $name
     * @return mixed
     */
    public function setLastReplyBy($name)
    {
        return $this->setData(self::LAST_REPLY_BY, $name);
    }

    /**
     * Get last reply date
     *
     * @return mixed
     */
    public function getLastReplyDate()
    {
        return $this->_get(self::LAST_REPLY_DATE);
    }

    /**
     * Set last reply date
     *
     * @param $replyDate
     * @return mixed
     */
    public function setLastReplyDate($replyDate)
    {
        return $this->setData(self::LAST_REPLY_DATE, $replyDate);
    }

    /**
     * Get customer messages
     *
     * @return mixed
     */
    public function getCustomerMessages()
    {
        return $this->_get(self::CUSTOMER_MESSAGES);
    }

    /**
     * Set customer messages
     *
     * @param $customerMessagesCount
     * @return mixed
     */
    public function setCustomerMessages($customerMessagesCount)
    {
        return $this->setData(self::CUSTOMER_MESSAGES, $customerMessagesCount);
    }

    /**
     * Get agent messages
     *
     * @return mixed
     */
    public function getAgentMessages()
    {
        return $this->_get(self::AGENT_MESSAGES);
    }

    /**
     * Set agent messages
     *
     * @param $agentMessagesCount
     * @return mixed
     */
    public function setAgentMessages($agentMessagesCount)
    {
        return $this->setData(self::AGENT_MESSAGES, $agentMessagesCount);
    }

    /**
     * Get first message
     *
     * @return mixed
     */
    public function getFirstMessageContent()
    {
        return $this->_get(self::FIRST_MESSAGE_CONTENT);
    }

    /**
     * Set first message
     *
     * @param $firstMessageContent
     * @return mixed
     */
    public function setFirstMessageContent($firstMessageContent)
    {
        return $this->setData(self::FIRST_MESSAGE_CONTENT, $firstMessageContent);
    }

    /**
     * Get data by key
     *
     * @param $key
     * @return mixed|null
     */
    public function getData($key)
    {
        return $this->_get($key);
    }

    public function toArray()
    {
        return $this->__toArray();
    }
}
