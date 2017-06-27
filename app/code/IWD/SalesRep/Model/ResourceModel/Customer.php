<?php

namespace IWD\SalesRep\Model\ResourceModel;

/**
 * Class Customer
 * @package IWD\SalesRep\Model\ResourceModel
 */
class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'iwd_sales_representative_attached_customer';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
