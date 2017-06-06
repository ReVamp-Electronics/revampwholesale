<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel\Config;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Config
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\Config', 'Aheadworks\Helpdesk\Model\ResourceModel\Config');
    }
}
