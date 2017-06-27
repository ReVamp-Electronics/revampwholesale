<?php

namespace IWD\SalesRep\Model\ResourceModel;

/**
 * Class B2BCustomer
 * @package IWD\SalesRep\Model\ResourceModel
 */
class B2BCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'iwd_sales_representative_b2bcustomer';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
