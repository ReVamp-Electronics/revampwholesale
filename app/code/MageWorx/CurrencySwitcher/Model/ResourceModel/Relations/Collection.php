<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Model\ResourceModel\Relations;

/**
 * Relations collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageWorx\CurrencySwitcher\Model\Relations', 'MageWorx\CurrencySwitcher\Model\ResourceModel\Relations');
    }
}
