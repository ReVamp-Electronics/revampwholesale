<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Ticket;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;

/**
 * Class Department
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class Department implements OptionSourceInterface
{
    /**
     * @var PermissionValidator
     */
    private $permissionValidator;

    /**
     * @var CollectionFactory
     */
    private $departmentCollectionFactory;

    /**
     * @param PermissionValidator $permissionValidator
     * @param CollectionFactory $departmentCollectionFactory
     */
    public function __construct(
        PermissionValidator $permissionValidator,
        CollectionFactory $departmentCollectionFactory
    ) {
        $this->permissionValidator = $permissionValidator;
        $this->departmentCollectionFactory = $departmentCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var DepartmentCollection $collection */
        $collection = $this->departmentCollectionFactory->create();
        $collection->addFieldToFilter(DepartmentInterface::IS_ENABLED, true);

        $departmentOptions = [];
        foreach ($collection as $item) {
            $departmentOptions[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }
        return $departmentOptions;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $optionsArray = $this->toOptionArray();
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Get available options
     * @return array
     */
    public function getAvailableOptions()
    {
        $allOptions = $this->getOptions();
        $newOptions = [
            'none' => __('Please select')
        ];
        return $newOptions + $allOptions;
    }

    /**
     * Get available options
     * @return array
     */
    public function getAvailableOptionsForView()
    {
        return $this->getAvailableOptionsForCurrentAgent(
            [
                DepartmentPermissionInterface::TYPE_VIEW,
                DepartmentPermissionInterface::TYPE_UPDATE,
            ]
        );
    }

    public function getAvailableOptionsForUpdate()
    {
        return $this->getAvailableOptionsForCurrentAgent(DepartmentPermissionInterface::TYPE_UPDATE);
    }

    /**
     * Get available options for current agent
     * @param int[] $permissionType
     * @return array
     */
    public function getAvailableOptionsForCurrentAgent($permissionType)
    {
        $userRoles = $this->permissionValidator->getUserRoles();

        /** @var DepartmentCollection $collection */
        $collection = $this->departmentCollectionFactory->create();
        $collection
            ->addFieldToFilter(DepartmentInterface::IS_ENABLED, true)
            ->addPermissionFilter($userRoles, $permissionType)
        ;

        $roleWebsiteIds = [];
        foreach ($userRoles as $roleId) {
            $roleRestriction = $this->permissionValidator->getRoleScopeRestriction($roleId);
            if ($roleRestriction) {
                foreach ($roleRestriction['websites'] as $websiteId) {
                    $roleWebsiteIds[] = $websiteId;
                }
            }
        }
        if (count($roleWebsiteIds) > 0) {
            $collection->addWebsiteFilter($roleWebsiteIds);
        }

        $departmentOptions = [];
        foreach ($collection as $item) {
            $departmentOptions[$item->getId()] = $item->getName();
        }
        return $departmentOptions;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return string|null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
