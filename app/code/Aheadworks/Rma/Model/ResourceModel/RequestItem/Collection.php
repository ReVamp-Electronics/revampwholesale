<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel\RequestItem;

use  \Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Aheadworks\Rma\Model\ResourceModel\RequestItem
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\RequestItem', 'Aheadworks\Rma\Model\ResourceModel\RequestItem');
    }

    public function addRequestFilter($requestId)
    {
        return $this->addFieldToFilter('request_id', ['eq' => $requestId]);
    }

    public function addOrderItemFilter($itemId)
    {
        return $this->addFieldToFilter('item_id', ['eq' => $itemId]);
    }

    public function addOrderFilter($orderId)
    {
        return $this->addFieldToFilter('order_id', ['eq' => $orderId]);
    }

    public function addRequestStatusFilter($statusIds)
    {
        return $this->addFieldToFilter('status_id', ['in' => $statusIds]);
    }

    public function joinRequest()
    {
        $this->getSelect()
            ->join(
                ['request_table' => $this->getTable('aw_rma_request')],
                'main_table.request_id = request_table.id',
                [
                    'order_id' => 'request_table.order_id',
                    'status_id' => 'request_table.status_id'
                ]
            )
        ;
        return $this;
    }

    public function joinOrderItem()
    {
        $this->getSelect()
            ->joinLeft(
                ['order_item_table' => $this->getTable('sales_order_item')],
                'main_table.item_id = order_item_table.item_id',
                [
                    'product_type' => 'order_item_table.product_type',
                    'name' => 'order_item_table.name',
                    'sku' => 'order_item_table.sku',
                    'is_virtual' => 'order_item_table.is_virtual'
                ]
            )
            ->joinLeft(
                ['products' => $this->getTable('catalog_product_entity')],
                'order_item_table.product_id = products.entity_id',
                ['product_id' => 'products.entity_id']
            )
            ->joinLeft(
                ['parent_item_table_1' => $this->getTable('sales_order_item')],
                "parent_item_table_1.item_id = order_item_table.parent_item_id AND parent_item_table_1.product_type = 'configurable'",
                [
                    'base_price' => 'IFNULL(parent_item_table_1.base_price, order_item_table.base_price)',
                    'price' => 'IFNULL(parent_item_table_1.price, order_item_table.price)'
                ]
            )
            ->joinLeft(
                ['parent_item_table_2' => $this->getTable('sales_order_item')],
                "parent_item_table_2.item_id = order_item_table.parent_item_id",
                [
                    'parent_product_id' => 'parent_item_table_2.product_id'
                ]
            )
        ;
        return $this;
    }

    /**
     * Adds 'reason' column with system custom field "Reason" value for specified $store_id
     * @param int $store_id
     * @return $this
     */
    public function joinReason($store_id = Store::DEFAULT_STORE_ID)
    {
        $this->getSelect()
            ->joinLeft(
                ['cf_value' => $this->getTable('aw_rma_request_item_custom_field_value')],
                'main_table.id = cf_value.entity_id',
                []
            )->joinLeft(
                ['cf' => $this->getTable('aw_rma_custom_field')],
                'cf.id = cf_value.field_id',
                []
            )->joinLeft(
                ['cf_option_value' => $this->getTable('aw_rma_custom_field_option_value')],
                "cf_option_value.option_id = cf_value.value AND cf_option_value.store_id = {$store_id}",
                ['reason' => 'cf_option_value.value as reason']
            )->where('cf.name = "Reason"')
        ;
        return $this;
    }
}