<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Api\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface DepartmentPermissionInterface
 * @package Aheadworks\Helpdesk\Api\Data
 * @api
 */
interface DepartmentPermissionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const VIEW_ROLE_IDS     = 'view_role_ids';
    const UPDATE_ROLE_IDS   = 'update_role_ids';
    const ASSIGN_ROLE_IDS   = 'assign_role_ids';
    /**#@-*/

    /**
     * Code of all roles
     */
    const ALL_ROLES_ID       = 0;

    /**#@+
     * Permission type constants
     */
    const TYPE_VIEW     = 1;
    const TYPE_UPDATE   = 2;
    const TYPE_ASSIGN   = 3;
    /**#@-*/

    /**
     * Get view role ids
     *
     * @return int[]
     */
    public function getViewRoleIds();

    /**
     * Set view role ids
     *
     * @param int[] $roleIds
     * @return $this
     */
    public function setViewRoleIds($roleIds);

    /**
     * Get update role ids
     *
     * @return int[]
     */
    public function getUpdateRoleIds();

    /**
     * Set update role ids
     *
     * @param int[] $roleIds
     * @return $this
     */
    public function setUpdateRoleIds($roleIds);

    /**
     * Get assign role ids
     *
     * @return int[]
     */
    public function getAssignRoleIds();

    /**
     * Set assign role ids
     *
     * @param int[] $roleIds
     * @return $this
     */
    public function setAssignRoleIds($roleIds);
}
