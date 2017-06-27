<?php

namespace IWD\MultiInventory\Model\ResourceModel\Warehouses;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Source
 * @package IWD\MultiInventory\Model\ResourceModel\Warehouses
 */
class Source extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('iwd_cataloginventory_stock', 'stock_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $iwdCataloginventoryStock = $this->getTable('iwd_cataloginventory_stock');

        $select->joinLeft(
            ['stock_address' => $this->getTable('iwd_cataloginventory_stock_address')],
            $iwdCataloginventoryStock . '.stock_id = stock_address.stock_id',
            [
                'city',
                'street',
                'region_id',
                'region',
                'postcode',
                'country_id'
            ]
        );

        return $select;
    }
}
