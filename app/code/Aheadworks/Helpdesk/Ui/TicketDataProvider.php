<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui;

use \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid\CollectionFactory;
use Aheadworks\Helpdesk\Model\Source\Ticket\Department as TicketDepartmentSource;
use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;

/**
 * Class TicketDataProvider
 * @package Aheadworks\Helpdesk\Ui
 */
class TicketDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection factory
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid\CollectionFactory
     */
    protected $collection;

    /**
     * Field strategies
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * Filter strategies
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

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
     * @param CollectionFactory $collectionFactory
     * @param TicketDepartmentSource $ticketDepartmentSource
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
        CollectionFactory $collectionFactory,
        TicketDepartmentSource $ticketDepartmentSource,
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
        $this->ticketDepartmentSource = $ticketDepartmentSource;
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
        $availableDepartments = $this->ticketDepartmentSource->getAvailableOptionsForView();
        $departmentIds = array_keys($availableDepartments);
        $this->getCollection()
            ->addFieldToFilter('department_id', $departmentIds);

        $roles = $this->permissionValidator->getUserRoles();
        $storeIds = [];
        foreach ($roles as $roleId) {
            $roleRestriction = $this->permissionValidator->getRoleScopeRestriction($roleId);
            if ($roleRestriction) {
                foreach ($roleRestriction['stores'] as $storeId) {
                    $storeIds[] = $storeId;
                }
            }
        }
        if (count($storeIds) > 0) {
            $this->getCollection()
                ->addFieldToFilter('store_id', $storeIds);
        }
    }
}
