<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Setup;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package Aheadworks\Helpdesk\Setup
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (
            $context->getVersion()
            && version_compare($context->getVersion(), '1.2.0', '<')
        ) {
            $this->addDefaultPermissions($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add default department permissions
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addDefaultPermissions(ModuleDataSetupInterface $setup)
    {
        $select = $setup->getConnection()->select()
            ->from($setup->getTable('aw_helpdesk_department'), ['id'])
        ;
        $departments = $setup->getConnection()->fetchAll($select);

        $permissionRows = [];
        foreach ($departments as $department) {
            $permissionRows[] = [
                'department_id' => $department['id'],
                'role_id' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'type' => DepartmentPermissionInterface::TYPE_VIEW
            ];
            $permissionRows[] = [
                'department_id' => $department['id'],
                'role_id' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'type' => DepartmentPermissionInterface::TYPE_UPDATE
            ];
            $permissionRows[] = [
                'department_id' => $department['id'],
                'role_id' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'type' => DepartmentPermissionInterface::TYPE_ASSIGN
            ];
        }
        if (count($permissionRows) > 0) {
            $setup->getConnection()->insertMultiple(
                $setup->getTable('aw_helpdesk_department_permission'),
                $permissionRows
            );
        }
        return $this;
    }
}
