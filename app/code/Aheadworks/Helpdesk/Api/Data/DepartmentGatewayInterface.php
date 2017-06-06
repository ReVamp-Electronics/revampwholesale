<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Api\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface DepartmentGatewayInterface
 * @package Aheadworks\Helpdesk\Api\Data
 * @api
 */
interface DepartmentGatewayInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const DEPARTMENT_ID     = 'department_id';
    const DEFAULT_STORE_ID  = 'default_store_id';
    const IS_ENABLED        = 'is_enabled';
    const PROTOCOL          = 'protocol';
    const HOST              = 'host';
    const EMAIL             = 'email';
    const LOGIN             = 'login';
    const PASSWORD          = 'password';
    const SECURE_TYPE       = 'secure_type';
    const PORT              = 'port';
    const IS_DELETE_PARSED  = 'is_delete_parsed';
    /**#@-*/

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get department id
     *
     * @return int
     */
    public function getDepartmentId();

    /**
     * Set department id
     *
     * @param int $departmentId
     * @return $this
     */
    public function setDepartmentId($departmentId);

    /**
     * Get default store id
     *
     * @return int
     */
    public function getDefaultStoreId();

    /**
     * Set default store id
     *
     * @param int $defaultStoreId
     * @return $this
     */
    public function setDefaultStoreId($defaultStoreId);

    /**
     * Get is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsEnabled();

    /**
     * Set is enabled
     *
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled);

    /**
     * Get protocol
     *
     * @return string
     */
    public function getProtocol();

    /**
     * Set protocol
     *
     * @param string $protocol
     * @return $this
     */
    public function setProtocol($protocol);

    /**
     * Get host
     *
     * @return string
     */
    public function getHost();

    /**
     * Set host
     *
     * @param string $host
     * @return $this
     */
    public function setHost($host);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin();

    /**
     * Set login
     *
     * @param string $login
     * @return $this
     */
    public function setLogin($login);

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword();

    /**
     * Set password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password);

    /**
     * Get secure type
     *
     * @return string
     */
    public function getSecureType();

    /**
     * Set secure type
     *
     * @param string $secureType
     * @return $this
     */
    public function setSecureType($secureType);

    /**
     * Get port
     *
     * @return string|null
     */
    public function getPort();

    /**
     * Set port
     *
     * @param string $port
     * @return $this
     */
    public function setPort($port);

    /**
     * Get is delete parsed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDeleteParsed();

    /**
     * Set is delete parsed
     *
     * @param bool $isDeleteParsed
     * @return $this
     */
    public function setIsDeleteParsed($isDeleteParsed);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return DepartmentGatewayExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param DepartmentGatewayExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(DepartmentGatewayExtensionInterface $extensionAttributes);
}
