<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Department;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Aheadworks\Helpdesk\Model\Department as DepartmentModel;
use Aheadworks\Helpdesk\Model\ResourceModel\Department as DepartmentResource;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Department
 * @codeCoverageIgnore
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(DepartmentModel::class, DepartmentResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachWebsites();
        $this->attachLabels();
        $this->attachGateways();
        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinWebsiteLinkageTable();
        $this->joinPermissionLinkageTable();
        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'website_ids') {
            $this->addFilter('website_id', ['in' => $condition], 'public');
            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add website filter
     *
     * @param int|array $website
     * @return $this
     */
    public function addWebsiteFilter($website)
    {
        if (!is_array($website)) {
            $website = [$website];
        }

        $this->addFilter('website_id', ['in' => $website], 'public');

        return $this;
    }

    /**
     * Attach websites to collection items
     *
     * @return void
     */
    private function attachWebsites()
    {
        $departmentIds = $this->getColumnValues('id');
        if (count($departmentIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['website_linkage_table' => $this->getTable('aw_helpdesk_department_website')])
                ->where('website_linkage_table.department_id IN (?)', $departmentIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $websites = [];
                $departmentId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['department_id'] == $departmentId) {
                        $websites[] = $data['website_id'];
                    }
                }
                $item->setData('website_ids', $websites);
            }
        }
    }

    /**
     * Attach labels to collection items
     *
     * @return void
     */
    private function attachLabels()
    {
        $departmentIds = $this->getColumnValues('id');
        if (count($departmentIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['label_linkage_table' => $this->getTable('aw_helpdesk_department_label')])
                ->where('label_linkage_table.department_id IN (?)', $departmentIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $labels = [];
                $departmentId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['department_id'] == $departmentId) {
                        $labels[] = [
                            'store_id' => $data['store_id'],
                            'label' => $data['label']
                        ];
                    }
                }
                $item->setData('store_labels', $labels);
            }
        }
    }

    /**
     * Attach gateways to collection items
     *
     * @return void
     */
    private function attachGateways()
    {
        $departmentIds = $this->getColumnValues('id');
        if (count($departmentIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['gateway_linkage_table' => $this->getTable('aw_helpdesk_department_gateway')])
                ->where('gateway_linkage_table.department_id IN (?)', $departmentIds);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $gateway = null;
                $departmentId = $item->getData('id');
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data[DepartmentGatewayInterface::DEPARTMENT_ID] == $departmentId) {
                        $gateway = $data;
                    }
                }
                $item->setData('gateway', $gateway);
            }
        }
    }

    /**
     * Join to website linkage table if website filter is applied
     *
     * @return void
     */
    private function joinWebsiteLinkageTable()
    {
        if ($this->getFilter('website_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['website_linkage_table' => $this->getTable('aw_helpdesk_department_website')],
                'main_table.id = website_linkage_table.department_id',
                []
            )
                ->group('main_table.id');
        }
    }

    /**
     * Add permission filter
     *
     * @param int|array $role
     * @param int|array|null $type
     * @return $this
     */
    public function addPermissionFilter($role, $type = null)
    {
        if (!is_array($role)) {
            $role = [$role];
        }
        if (!in_array(DepartmentPermissionInterface::ALL_ROLES_ID, $role)) {
            array_unshift($role, (string)DepartmentPermissionInterface::ALL_ROLES_ID);
        }
        $this->addFilter('role_id', ['in' => $role], 'public');

        if ($type && !is_array($type) ) {
            $type = [$type];
            $this->addFilter('type', ['in' => $type], 'public');
        }

        return $this;
    }


    /**
     * Join to permission linkage table if permission filter is applied
     *
     * @return void
     */
    private function joinPermissionLinkageTable()
    {
        if ($this->getFilter('role_id') || $this->getFilter('type')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['permission_linkage_table' => $this->getTable('aw_helpdesk_department_permission')],
                'main_table.id = permission_linkage_table.department_id',
                []
            )
                ->group('main_table.id');
        }
    }
}
