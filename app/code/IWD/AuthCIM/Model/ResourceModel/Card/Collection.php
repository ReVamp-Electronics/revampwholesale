<?php

namespace IWD\AuthCIM\Model\ResourceModel\Card;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package IWD\AuthCIM\Model\ResourceModel\Card
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'IWD\AuthCIM\Model\Card',
            'IWD\AuthCIM\Model\ResourceModel\Card'
        );
    }
}
