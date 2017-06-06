<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Aheadworks\Helpdesk\Api\Data\DepartmentExtensionInterface;

/**
 * Class Department
 * @package Aheadworks\Helpdesk\Model\Data
 * @codeCoverageIgnore
 */
class Department extends AbstractExtensibleObject implements DepartmentInterface
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
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->_get(self::WEBSITE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteIds($websiteIds)
    {
        return $this->setData(self::WEBSITE_IDS, $websiteIds);
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
    public function getIsVisible()
    {
        return $this->_get(self::IS_VISIBLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVisible($isVisible)
    {
        return $this->setData(self::IS_VISIBLE, $isVisible);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->_get(self::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT, $isDefault);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabels()
    {
        return $this->_get(self::STORE_LABELS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreLabels($storelabels)
    {
        return $this->setData(self::STORE_LABELS, $storelabels);
    }

    /**
     * {@inheritdoc}
     */
    public function getGateway()
    {
        return $this->_get(self::GATEWAY);
    }

    /**
     * {@inheritdoc}
     */
    public function setGateway($gateway)
    {
        return $this->setData(self::GATEWAY, $gateway);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions()
    {
        return $this->_get(self::PERMISSIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPermissions($permissions)
    {
        return $this->setData(self::PERMISSIONS, $permissions);
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
    public function setExtensionAttributes(DepartmentExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
