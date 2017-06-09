<?php

namespace IWD\OrderManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Log
 * @package IWD\OrderManager\Model\ResourceModel
 */
class Log extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('iwd_om_log', 'id');
    }
}
