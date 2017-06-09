<?php

namespace IWD\SalesRep\Model\ResourceModel;

/**
 * Class Order
 * @package IWD\SalesRep\Model\ResourceModel
 */
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'iwd_sales_representative_order';
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
