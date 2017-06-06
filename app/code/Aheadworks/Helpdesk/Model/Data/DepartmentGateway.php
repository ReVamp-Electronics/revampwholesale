<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayExtensionInterface;

/**
 * Class DepartmentGateway
 * @package Aheadworks\Helpdesk\Model\Data
 * @codeCoverageIgnore
 */
class DepartmentGateway extends AbstractExtensibleObject implements DepartmentGatewayInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getDepartmentId()
    {
        return $this->_get(self::DEPARTMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDepartmentId($departmentId)
    {
        return $this->setData(self::DEPARTMENT_ID, $departmentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultStoreId()
    {
        return $this->_get(self::DEFAULT_STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultStoreId($defaultStoreId)
    {
        return $this->setData(self::DEFAULT_STORE_ID, $defaultStoreId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsEnabled()
    {
        return $this->_get(self::IS_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsEnabled($isEnabled)
    {
        return $this->setData(self::IS_ENABLED, $isEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocol()
    {
        return $this->_get(self::PROTOCOL);
    }

    /**
     * {@inheritdoc}
     */
    public function setProtocol($protocol)
    {
        return $this->setData(self::PROTOCOL, $protocol);
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->_get(self::HOST);
    }

    /**
     * {@inheritdoc}
     */
    public function setHost($host)
    {
        return $this->setData(self::HOST, $host);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogin()
    {
        return $this->_get(self::LOGIN);
    }

    /**
     * {@inheritdoc}
     */
    public function setLogin($login)
    {
        return $this->setData(self::LOGIN, $login);
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->_get(self::PASSWORD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * {@inheritdoc}
     */
    public function getSecureType()
    {
        return $this->_get(self::SECURE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSecureType($secureType)
    {
        return $this->setData(self::SECURE_TYPE, $secureType);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->_get(self::PORT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPort($port)
    {
        return $this->setData(self::PORT, $port);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDeleteParsed()
    {
        return $this->_get(self::IS_DELETE_PARSED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDeleteParsed($isDeleteParsed)
    {
        return $this->setData(self::IS_DELETE_PARSED, $isDeleteParsed);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(DepartmentGatewayExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
