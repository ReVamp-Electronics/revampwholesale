<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Api\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface DepartmentInterface
 * @package Aheadworks\Helpdesk\Api\Data
 * @api
 */
interface DepartmentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const NAME          = 'name';
    const WEBSITE_IDS   = 'website_ids';
    const IS_ENABLED    = 'is_enabled';
    const IS_VISIBLE    = 'is_visible';
    const IS_DEFAULT    = 'is_default';
    const STORE_LABELS  = 'store_labels';
    const GATEWAY       = 'gateway';
    const PERMISSIONS   = 'permissions';
    /**#@-*/

    /**
     * Get department id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set department id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get department name
     *
     * @return string
     */
    public function getName();

    /**
     * Set department name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get website ids
     *
     * @return int[]
     */
    public function getWebsiteIds();

    /**
     * Set website ids
     *
     * @param int[] $websiteIds
     * @return $this
     */
    public function setWebsiteIds($websiteIds);

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
     * Get is visible on store
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVisible();

    /**
     * Set is visible on store
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible);

    /**
     * Get is default
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDefault();

    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault);

    /**
     * Get store labels
     *
     * @return \Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface[]|null
     */
    public function getStoreLabels();

    /**
     * Set store labels
     *
     * @param \Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface[]|null $storelabels
     * @return $this
     */
    public function setStoreLabels($storelabels);

    /**
     * Get gateway
     *
     * @return \Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface|null
     */
    public function getGateway();

    /**
     * Set gateway
     *
     * @param \Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface|null $gateway
     * @return $this
     */
    public function setGateway($gateway);

    /**
     * Get permissions
     *
     * @return \Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface
     */
    public function getPermissions();

    /**
     * Set permissions
     *
     * @param \Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface $permissions
     * @return $this
     */
    public function setPermissions($permissions);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return DepartmentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param DepartmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(DepartmentExtensionInterface $extensionAttributes);
}
