<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;

/**
 * Class Roles
 * @package Aheadworks\Helpdesk\Model\Source
 */
class Roles implements OptionSourceInterface
{
    /**
     * @var RoleCollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var []
     */
    private $roles;

    /**
     * @param RoleCollectionFactory $roleCollectionFactory
     */
    public function __construct(
        RoleCollectionFactory $roleCollectionFactory
    ) {
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->roles) {
            /** @var RoleCollection $roleCollection */
            $roleCollection = $this->roleCollectionFactory->create();
            $roleCollection->setRolesFilter();
            $roles = [];
            $roles[] = [
                'value' => DepartmentPermissionInterface::ALL_ROLES_ID,
                'label' => __('All roles'),
            ];
            foreach ($roleCollection as $role) {
                $roles[] = [
                    'value' => $role->getData('role_id'),
                    'label' => $role->getData('role_name'),
                ];
            }
            $this->roles = $roles;
        }
        return $this->roles;
    }
}
