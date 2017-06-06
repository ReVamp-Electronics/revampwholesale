<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Api\Data;

/**
 * Interface TicketInterface
 * @package Aheadworks\Helpdesk\Api\Data
 */
interface TicketInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const UID = 'uid';
    const DEPARTMENT_ID = 'department_id';
    const CREATED_AT = 'created_at';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_NAME = 'customer_name';
    const SUBJECT = 'subject';
    const STATUS = 'status';
    const PRIORITY = 'priority';
    const AGENT_ID = 'agent_id';
    const ORDER_ID = 'order_id';
    const CC_RECIPIENTS = 'cc_recipients';
    const STORE_ID = 'store_id';

    /**
     * Get ticket id
     *
     * @api
     * @return int|null
     */
    public function getId();

    /**
     * Set ticket id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get uid
     *
     * @api
     * @return string
     */
    public function getUid();

    /**
     * Set uid
     *
     * @api
     * @param string $uid
     * @return $this
     */
    public function setUid($uid);

    /**
     * Get department id
     *
     * @api
     * @return int
     */
    public function getDepartmentId();

    /**
     * Set department id
     *
     * @api
     * @param int $departmentId
     * @return $this
     */
    public function setDepartmentId($departmentId);

    /**
     * Get created at
     *
     * @api
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @api
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get customer id
     *
     * @api
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @api
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get customer email
     *
     * @api
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     *
     * @api
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get customer name
     *
     * @api
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set customer name
     *
     * @api
     * @param string $customerName
     * @return $this
     */
    public function setCustomerName($customerName);

    /**
     * Get ticket subject
     *
     * @api
     * @return string
     */
    public function getSubject();

    /**
     * Set ticket subject
     *
     * @api
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get ticket status
     *
     * @api
     * @return string
     */
    public function getStatus();

    /**
     * Set ticket status
     *
     * @api
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get ticket priority
     *
     * @api
     * @return string
     */
    public function getPriority();

    /**
     * Set ticket priority
     *
     * @api
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * Get agent id
     *
     * @api
     * @return int|null
     */
    public function getAgentId();

    /**
     * Set agent id
     *
     * @api
     * @param int $agentId
     * @return $this
     */
    public function setAgentId($agentId);

    /**
     * Get order id
     *
     * @api
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @api
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get CC Recipients
     *
     * @api
     * @return string|null
     */
    public function getCcRecipients();

    /**
     * Set CC Recipients
     *
     * @api
     * @param string $ccRecipients
     * @return $this
     */
    public function setCcRecipients($ccRecipients);

    /**
     * Get store id
     *
     * @api
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @api
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get data
     *
     * @api
     * @param int|string $key
     * @return mixed
     */
    public function getData($key);

    /**
     * Set data
     *
     * @api
     * @param int|string $key
     * @param mixed $value
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
