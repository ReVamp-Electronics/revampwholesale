<?php

namespace IWD\AuthCIM\Model\ResourceModel\Refund;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package IWD\AuthCIM\Model\ResourceModel\Refund
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\AuthCIM\Model\Refund',
            'IWD\AuthCIM\Model\ResourceModel\Refund'
        );
    }
}
