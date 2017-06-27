<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Model\ResourceModel\Request;

/**
 * Class Collection
 * @package Aheadworks\Rma\Model\ResourceModel\Request
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\Request', 'Aheadworks\Rma\Model\ResourceModel\Request');
    }

    public function addCustomerFilter($customerId)
    {
        return $this->addFieldToFilter('main_table.customer_id', ['eq' => $customerId]);
    }

    public function joinCustomFieldValues($customFieldCollection) {
        foreach ($customFieldCollection as $customField) {
            $tableName = 'cf' . $customField->getId();
            $this->getSelect()
                ->joinLeft(
                    [$tableName => $this->getTable('aw_rma_request_custom_field_value')],
                    "main_table.id = {$tableName}.entity_id AND {$tableName}.field_id = {$customField->getId()}",
                    "{$tableName}.value as {$tableName}_value"
                );
        }
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function joinStatusAttributeValues($attributes)
    {
        foreach ($attributes as $attrCode) {
            $conditions = [
                "main_table.status_id = {$attrCode}.status_id",
                "main_table.store_id = {$attrCode}.store_id",
                 $this->getConnection()->quoteInto("attribute_code = ?", $attrCode)
            ];
            $this->getSelect()
                ->joinLeft(
                    [$attrCode => $this->getTable('aw_rma_status_attr_value')],
                    implode(' AND ', $conditions),
                    [
                        'status_' . $attrCode => $attrCode.'.value'
                    ]
                );
        }
        return $this;
    }

    public function joinOrders()
    {
        $this->getSelect()
            ->joinLeft(
                ['order_table' => $this->getTable('sales_order')],
                'main_table.order_id = order_table.entity_id',
                [
                    'order_increment_id' => 'order_table.increment_id'
                ]
            );
        return $this;
    }
}