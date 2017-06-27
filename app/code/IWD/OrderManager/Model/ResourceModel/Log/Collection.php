<?php

namespace IWD\OrderManager\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package IWD\OrderManager\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\OrderManager\Model\Log',
            'IWD\OrderManager\Model\ResourceModel\Log'
        );
    }
}
