<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Permission;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Api\DataObjectHelper;


/**
 * Class ReadHandler
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department\Relation\Permission
 * @codeCoverageIgnore
 */
class ReadHandler implements ExtensionInterface
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DepartmentPermissionInterfaceFactory
     */
    private $departmentPermissionFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param DepartmentPermissionInterfaceFactory $departmentPermissionFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        DepartmentPermissionInterfaceFactory $departmentPermissionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->departmentPermissionFactory = $departmentPermissionFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(DepartmentInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from(
                    $this->resourceConnection->getTableName('aw_helpdesk_department_permission'),
                    ['role_id', 'type']
                )
                ->where('department_id = :id');
            $permissions = $connection->fetchAll($select, ['id' => $entityId]);

            /** @var DepartmentPermissionInterface $permissionsObject */
            $permissionsObject = $this->departmentPermissionFactory->create();
            $viewRoles = [];
            $updateRoles = [];
            $assignRoles = [];

            foreach ($permissions as $permissionData) {
                switch ($permissionData['type']) {
                    case DepartmentPermissionInterface::TYPE_VIEW:
                        $viewRoles[] = $permissionData['role_id'];
                        break;
                    case DepartmentPermissionInterface::TYPE_UPDATE:
                        $updateRoles[] = $permissionData['role_id'];
                        break;
                    case DepartmentPermissionInterface::TYPE_ASSIGN:
                        $assignRoles[] = $permissionData['role_id'];
                        break;
                }
            }
            $permissionsObject
                ->setViewRoleIds($viewRoles)
                ->setUpdateRoleIds($updateRoles)
                ->setAssignRoleIds($assignRoles)
            ;
            $entity->setPermissions($permissionsObject);
        }
        return $entity;
    }
}
