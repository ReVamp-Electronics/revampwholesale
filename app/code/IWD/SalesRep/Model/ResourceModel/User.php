<?php

namespace IWD\SalesRep\Model\ResourceModel;

/**
 * Class User
 * @package IWD\SalesRep\Model\ResourceModel
 */
class User extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'iwd_sales_representative_user';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
