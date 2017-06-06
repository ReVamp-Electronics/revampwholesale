<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Permission;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Aheadworks\Helpdesk\Api\Data\TicketInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Model\Config;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory as AdminUserFactory;
use Magento\User\Model\ResourceModel\User as UserResource;

/**
 * Class Validator
 * @package Aheadworks\Helpdesk\Model\Permission
 */
class Validator
{
    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * @var RoleCollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AdminUserFactory
     */
    private $adminUserFactory;

    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @param AdminSession $adminSession
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param AdminUserFactory $adminUserFactory
     * @param UserResource $userResource
     */
    public function __construct(
        AdminSession $adminSession,
        RoleCollectionFactory $roleCollectionFactory,
        DepartmentRepositoryInterface $departmentRepository,
        Config $config,
        StoreManagerInterface $storeManager,
        AdminUserFactory $adminUserFactory,
        UserResource $userResource
    ) {
        $this->adminSession = $adminSession;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->departmentRepository = $departmentRepository;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->adminUserFactory = $adminUserFactory;
        $this->userResource = $userResource;
    }

    /**
     * Validate by type
     *
     * @param int $type
     * @param TicketInterface $ticket
     * @param \Magento\User\Model\User|null $adminUser
     * @return bool
     */
    public function validate($type, $ticket, $adminUser = null)
    {
        $result = false;
        switch ($type) {
            case DepartmentPermissionInterface::TYPE_VIEW:
                $result = $this->viewValidate($ticket, $adminUser);
                break;
            case DepartmentPermissionInterface::TYPE_UPDATE:
                $result = $this->updateValidate($ticket, $adminUser);
                break;
            case DepartmentPermissionInterface::TYPE_ASSIGN:
                $result = $this->assignValidate($ticket, $adminUser);
                break;
        }
        return $result;
    }

    /**
     * Validate permission to view the ticket
     *
     * @param TicketInterface $ticket
     * @param \Magento\User\Model\User|null $adminUser
     * @return bool
     */
    public function viewValidate($ticket, $adminUser = null)
    {
        if ($adminUser == null) {
            $adminUser = $this->adminSession->getUser();
        }
        $userRoles = $this->getUserRoles($adminUser);

        /** @var DepartmentPermissionInterface $permissions */
        $permissions = $this->getPermissions($ticket->getDepartmentId());
        if ($permissions) {
            $viewRoleIds = array_merge($permissions->getViewRoleIds(), $permissions->getUpdateRoleIds());
            return $this->validateRoles($userRoles, $viewRoleIds);
        }
        return false;
    }

    /**
     * Validate permission to update the ticket
     *
     * @param TicketInterface $ticket
     * @param \Magento\User\Model\User|null $adminUser
     * @return bool
     */
    public function updateValidate($ticket, $adminUser = null)
    {
        if ($adminUser == null) {
            $adminUser = $this->adminSession->getUser();
        }
        $userRoles = $this->getUserRoles($adminUser);

        /** @var DepartmentPermissionInterface $permissions */
        $permissions = $this->getPermissions($ticket->getDepartmentId());
        if ($permissions) {
            $updateRoleIds = $permissions->getUpdateRoleIds();
            return $this->validateRoles($userRoles, $updateRoleIds);
        }
        return false;
    }

    /**
     * Validate permission to assign the ticket
     *
     * @param TicketInterface $ticket
     * @param \Magento\User\Model\User|null $adminUser
     * @return bool
     */
    public function assignValidate($ticket, $adminUser = null)
    {
        if ($adminUser == null) {
            $adminUser = $this->adminSession->getUser();
        }
        $userRoles = $this->getUserRoles($adminUser);

        /** @var DepartmentPermissionInterface $permissions */
        $permissions = $this->getPermissions($ticket->getDepartmentId());
        if ($permissions) {
            $assignRoleIds = $permissions->getAssignRoleIds();
            return $this->validateRoles($userRoles, $assignRoleIds);
        }
        return false;
    }

    /**
     * Validate user roles with specified roles
     *
     * @param int[] $userRoleIds
     * @param int[] $roleIds
     * @return bool
     */
    private function validateRoles($userRoleIds, $roleIds)
    {
        if (in_array(DepartmentPermissionInterface::ALL_ROLES_ID, $roleIds)) {
            return true;
        }

        foreach ($userRoleIds as $roleId) {
            if (in_array($roleId, $roleIds)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get admin user role
     *
     * @param \Magento\User\Model\User|null $user
     * @return []
     */
    public function getUserRoles($adminUser = null)
    {
        if ($adminUser == null) {
            $adminUser = $this->adminSession->getUser();
        }

        if ($adminUser->hasData('roles')) {
            $userRoles = $adminUser->getData('roles');
        } else {
            $userRoles = $adminUser->getRoles();
        }

        return $userRoles;
    }

    /**
     * Get role scope restriction if it is enabled (Magento EE)
     * ['websites' => [websiteIds], 'stores' => [storeIds]]
     *
     * @param int $userRoleId
     * @return []|bool
     */
    public function getRoleScopeRestriction($userRoleId)
    {
        if ($this->config->isEE()) {
            /** @var RoleCollection $roleCollection */
            $roleCollection = $this->roleCollectionFactory->create();
            $roleCollection->addFieldToFilter('role_id', $userRoleId);

            foreach ($roleCollection as $role) {
                if (!$role->getGwsIsAll()) {
                    $restrictedWebsites = $role->getGwsWebsites();
                    if ($restrictedWebsites) {
                        $websiteIds = explode(',', $restrictedWebsites);
                        $storeIds = [];
                        /** @var \Magento\Store\Api\Data\StoreInterface $store */
                        foreach ($this->storeManager->getStores() as $store) {
                            if (in_array($store->getWebsiteId(), $websiteIds)) {
                                $storeIds[] = $store->getId();
                            }
                        }
                    } else {
                        $restrictedStoreGroups = $role->getGwsStoreGroups();
                        $restrictedStoreGroups = explode(',', $restrictedStoreGroups);
                        $websiteIds = [];
                        $storeIds = [];
                        /** @var \Magento\Store\Api\Data\StoreInterface $store */
                        foreach ($this->storeManager->getStores() as $store) {
                            if (in_array($store->getStoreGroupId(), $restrictedStoreGroups)) {
                                if (!in_array($store->getWebsiteId(), $websiteIds)) {
                                    $websiteIds[] = $store->getWebsiteId();
                                }
                                $storeIds[] = $store->getId();
                            }
                        }
                    }
                    $result = [];
                    $result['websites'] = $websiteIds;
                    $result['stores'] = $storeIds;
                    return $result;
                }
            }
        }
        return false;
    }

    /**
     * Get permissions of the department
     *
     * @param int $departmentId
     * @return DepartmentPermissionInterface|bool
     */
    private function getPermissions($departmentId)
    {
        try {
            /** @var DepartmentInterface $department */
            $department = $this->departmentRepository->getById($departmentId);
            /** @var DepartmentPermissionInterface $permissions */
            $permissions = $department->getPermissions();
            return $permissions;
        } catch (NoSuchEntityException $e) {

        }
        return false;
    }

    /**
     * Get admin user by id
     *
     * @param int $userId
     * @return \Magento\User\Model\User|false
     */
    public function getAdminUser($userId)
    {
        $adminUser = $this->adminUserFactory->create();
        $this->userResource->load($adminUser, $userId);
        if ($adminUser->getId()) {
            return $adminUser;
        }
        return false;
    }
}
