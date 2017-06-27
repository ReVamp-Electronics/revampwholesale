<?php

namespace IWD\AuthCIM\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Refund
 * @package IWD\AuthCIM\Model\ResourceModel
 */
class Refund extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('iwd_authorizecim_refunds', 'id');
    }
}
