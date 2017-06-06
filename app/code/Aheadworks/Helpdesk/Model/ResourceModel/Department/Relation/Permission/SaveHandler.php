<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Permission;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Permission
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();

        /** @var DepartmentPermissionInterface $permissions */
        $permissions = $entity->getPermissions();

        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_helpdesk_department_permission');

        $connection->delete($tableName, ['department_id = ?' => (int)$entityId]);

        if ($permissions) {
            $data = [];
            $viewRoleIds = $this->prepareRoles($permissions->getViewRoleIds());
            $updateRoleIds = $this->prepareRoles($permissions->getUpdateRoleIds());
            $assignRoleIds = $this->prepareRoles($permissions->getAssignRoleIds());

            foreach ($viewRoleIds as $roleId) {
                $data[] = [
                    'department_id' => (int)$entityId,
                    'role_id'       => $roleId,
                    'type'          => DepartmentPermissionInterface::TYPE_VIEW,
                ];
            }
            foreach ($updateRoleIds as $roleId) {
                $data[] = [
                    'department_id' => (int)$entityId,
                    'role_id'       => $roleId,
                    'type'          => DepartmentPermissionInterface::TYPE_UPDATE,
                ];
            }
            foreach ($assignRoleIds as $roleId) {
                $data[] = [
                    'department_id' => (int)$entityId,
                    'role_id'       => $roleId,
                    'type'          => DepartmentPermissionInterface::TYPE_ASSIGN,
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }

        return $entity;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(DepartmentInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Prepare roles for save
     *
     * @param int[] $roles
     * @return int[]
     */
    private function prepareRoles($roles)
    {
        if (in_array(DepartmentPermissionInterface::ALL_ROLES_ID, $roles)) {
            $roles = [DepartmentPermissionInterface::ALL_ROLES_ID];
        }
        return $roles;
    }
}
