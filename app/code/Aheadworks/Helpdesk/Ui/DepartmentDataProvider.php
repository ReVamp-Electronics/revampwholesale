<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui;

use Aheadworks\Helpdesk\Model\ResourceModel\Department\Grid\CollectionFactory as DepartmentGridCollectionFactory;
use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;

/**
 * Class DepartmentDataProvider
 * @package Aheadworks\Helpdesk\Ui
 */
class DepartmentDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection factory
     * @var DepartmentGridCollectionFactory
     */
    protected $collection;

    /**
     * Field strategies
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    private $addFieldStrategies;

    /**
     * Filter strategies
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    private $addFilterStrategies;

    /**
     * @var TicketDepartmentSource
     */
    private $ticketDepartmentSource;

    /**
     * @var PermissionValidator
     */
    private $permissionValidator;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DepartmentGridCollectionFactory $collectionFactory
     * @param PermissionValidator $permissionValidator
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DepartmentGridCollectionFactory $collectionFactory,
        PermissionValidator $permissionValidator,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->permissionValidator = $permissionValidator;
        $this->addPermissionFilters();
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
                ->load()
            ;
        }
        return parent::getData();
    }

    /**
     * Apply permission filters
     */
    private function addPermissionFilters()
    {
        $roles = $this->permissionValidator->getUserRoles();
        $websiteIds = [];
        foreach ($roles as $roleId) {
            $roleRestriction = $this->permissionValidator->getRoleScopeRestriction($roleId);
            if ($roleRestriction) {
                foreach ($roleRestriction['websites'] as $websiteId) {
                    $websiteIds[] = $websiteId;
                }
            }
        }
        if (count($websiteIds) > 0) {
            $this->getCollection()
                ->addWebsiteFilter($websiteIds);
        }
    }
}
