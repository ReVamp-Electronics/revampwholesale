<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Api\Data;

/**
 * Interface TicketFlatInterface
 * @package Aheadworks\Helpdesk\Api\Data
 */
interface TicketFlatInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'entity_id';
    const TICKET_ID = 'ticket_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const AGENT_ID = 'agent_id';
    const AGENT_NAME = 'agent_name';
    const LAST_REPLY_TYPE = 'last_reply_type';
    const LAST_REPLY_BY = 'last_reply_by';
    const LAST_REPLY_DATE = 'last_reply_date';
    const CUSTOMER_MESSAGES = 'customer_messages';
    const AGENT_MESSAGES = 'agent_messages';
    const FIRST_MESSAGE_CONTENT = 'first_message_content';

    /**
     * Get flat id
     *
     * @api
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set flat id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setEntityId($id);

    /**
     * Get ticket id
     *
     * @api
     * @return int|null
     */
    public function getTicketId();

    /**
     * Set ticket id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setTicketId($id);

    /**
     * Get order increment ID
     *
     * @api
     * @return mixed
     */
    public function getOrderIncrementId();

    /**
     * Set order increment ID
     *
     * @param $incrementId
     * @return mixed
     */
    public function setOrderIncrementId($incrementId);

    /**
     * Get agent id
     *
     * @api
     * @return mixed
     */
    public function getAgentId();

    /**
     * Set agent id
     *
     * @api
     * @param $agentId
     * @return mixed
     */
    public function setAgentId($agentId);

    /**
     * Get agent name
     *
     * @api
     * @return mixed
     */
    public function getAgentName();

    /**
     * Set agent name
     *
     * @api
     * @param $agentName
     * @return mixed
     */
    public function setAgentName($agentName);

    /**
     * Get last reply type
     *
     * @api
     * @return mixed
     */
    public function getLastReplyType();

    /**
     * Set last reply type
     *
     * @api
     * @param $replyType
     * @return mixed
     */
    public function setLastReplyType($replyType);

    /**
     * Get last reply by
     *
     * @api
     * @return mixed
     */
    public function getLastReplyBy();

    /**
     * Set last reply by
     *
     * @api
     * @param $name
     * @return mixed
     */
    public function setLastReplyBy($name);

    /**
     * Get last reply date
     *
     * @api
     * @return mixed
     */
    public function getLastReplyDate();

    /**
     * Set last reply date
     *
     * @api
     * @param $replyDate
     * @return mixed
     */
    public function setLastReplyDate($replyDate);

    /**
     * Get customer messages
     *
     * @api
     * @return mixed
     */
    public function getCustomerMessages();

    /**
     * Set customer messages
     *
     * @api
     * @param $customerMessagesCount
     * @return mixed
     */
    public function setCustomerMessages($customerMessagesCount);

    /**
     * Get agent messages
     *
     * @api
     * @return mixed
     */
    public function getAgentMessages();

    /**
     * Set agent messages
     *
     * @api
     * @param $agentMessagesCount
     * @return mixed
     */
    public function setAgentMessages($agentMessagesCount);

    /**
     * Get first message
     *
     * @api
     * @return mixed
     */
    public function getFirstMessageContent();

    /**
     * Set first message
     *
     * @api
     * @param $firstMessageContent
     * @return mixed
     */
    public function setFirstMessageContent($firstMessageContent);

    /**
     * Get data
     *
     * @api
     * @param $key
     * @return mixed
     */
    public function getData($key);

    /**
     * Set data
     *
     * @api
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setData($key, $value);

    /**
     * Get all data as array
     *
     * @api
     * @return mixed
     */
    public function toArray();
}
