<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionExtensionInterface;

/**
 * Class DepartmentPermission
 * @package Aheadworks\Helpdesk\Model\Data
 * @codeCoverageIgnore
 */
class DepartmentPermission extends AbstractSimpleObject implements DepartmentPermissionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getViewRoleIds()
    {
        return $this->_get(self::VIEW_ROLE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setViewRoleIds($roleIds)
    {
        return $this->setData(self::VIEW_ROLE_IDS, $roleIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateRoleIds()
    {
        return $this->_get(self::UPDATE_ROLE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdateRoleIds($roleIds)
    {
        return $this->setData(self::UPDATE_ROLE_IDS, $roleIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignRoleIds()
    {
        return $this->_get(self::ASSIGN_ROLE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAssignRoleIds($roleIds)
    {
        return $this->setData(self::ASSIGN_ROLE_IDS, $roleIds);
    }
}
