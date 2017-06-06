<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Ticket;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Aheadworks\Helpdesk\Helper\Config as ConfigHelper;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;

/**
 * Class Agent
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class Agent implements OptionSourceInterface
{
    /**
     * Agent value of unassigned ticket
     */
    const UNASSIGNED_VALUE = '0';

    /**
     * User collection factory
     *
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @param UserCollectionFactory $userCollectionFactory
     * @param ConfigHelper $configHelper
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(
        UserCollectionFactory $userCollectionFactory,
        ConfigHelper $configHelper,
        DepartmentRepositoryInterface $departmentRepository
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->configHelper = $configHelper;
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $userCollection = $this->getUserCollection();
        $userOptions = [];
        foreach ($userCollection->getItems() as $item) {
            $userOptions[(string)$item->getUserId()] = $item->getUserFullname();
        }
        return $userOptions;
    }

    /**
     * Get available options
     * @return array
     */
    public function getAvailableOptions()
    {
        $allOptions = $this->getOptionArray();
        $availableAgents = $this->getAvailableAgents();

        $result = [];
        $unassigned = [self::UNASSIGNED_VALUE => __('Unassigned')];
        if (!$availableAgents) {
            $result = $allOptions;
            return $unassigned + $result;
        }
        foreach ($availableAgents as $agentId) {
            if (false === array_key_exists($agentId, $allOptions)) {
                continue;
            }
            $result[$agentId] = $allOptions[$agentId];
        }
        return $unassigned + $result;
    }

    /**
     * Get user collection
     *
     * @return mixed
     */
    protected function getUserCollection()
    {
        $userCollection = $this->userCollectionFactory->create();
        $userCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $userCollection
            ->getSelect()
            ->columns([
                'user_id' => 'main_table.user_id',
                'user_fullname' => 'CONCAT(main_table.firstname, " ", main_table.lastname)'
            ]);

        return $userCollection;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $userCollection = $this->getUserCollection();
        $userList = [];
        foreach ($userCollection->getItems() as $item) {
            $userList[] = ['value' => $item->getUserId(), 'label' => $item->getUserFullname()];
        }

        return $userList;
    }

    /**
     * Get available options for specified department id
     *
     * @param int $departmentId
     * @return []
     */
    public function getAvailableOptionsForDepartment($departmentId)
    {
        try {
            /** @var DepartmentInterface $department */
            $department = $this->departmentRepository->getById($departmentId);
            $updateRoleIds = $department->getPermissions()->getUpdateRoleIds();

            $userCollection = $this->getUserCollection();
            $allUsers = in_array(DepartmentPermissionInterface::ALL_ROLES_ID, $updateRoleIds);
            $userList = [];
            foreach ($userCollection->getItems() as $user) {
                if (!$allUsers) {
                    $userRoles = $user->getRoles();
                    if (count(array_intersect($userRoles, $updateRoleIds)) == 0) {
                        continue;
                    }
                }
                $userList[(string) $user->getUserId()] = $user->getUserFullname();
            }

            $availableAgents = $this->getAvailableAgents();
            $result = [];
            $unassigned = [self::UNASSIGNED_VALUE => __('Unassigned')];
            if (!$availableAgents) {
                $result = $userList;
                return $unassigned + $result;
            }
            foreach ($availableAgents as $agentId) {
                if (false === array_key_exists($agentId, $userList)) {
                    continue;
                }
                $result[$agentId] = $userList[$agentId];
            }
            return $unassigned + $result;
        } catch (NoSuchEntityException $e) {

        }
        return [];
    }

    /**
     * Get agent name by id
     *
     * @param string $agentId
     * @return string
     */
    public function getOptionLabelByValue($agentId)
    {
        $agents = $this->getOptionArray();
        $label = __('Unassigned');
        if (array_key_exists($agentId, $agents)) {
            $label = $agents[$agentId];
        }
        return $label;
    }

    /**
     * Get available agents
     * @return array
     */
    private function getAvailableAgents()
    {
        $availableAgents = $this->configHelper->getAgents();
        if (!$availableAgents) {
            $availableAgents = [];
        } else {
            $availableAgents = explode(',', $availableAgents);
        }
        return $availableAgents;
    }
}