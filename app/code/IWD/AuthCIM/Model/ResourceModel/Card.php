<?php

namespace IWD\AuthCIM\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Card
 * @package IWD\AuthCIM\Model\ResourceModel
 */
class Card extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('iwd_authorizecim_card', 'id');
    }
}
