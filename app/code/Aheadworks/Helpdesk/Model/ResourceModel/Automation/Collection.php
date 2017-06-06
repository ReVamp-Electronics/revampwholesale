<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel\Automation;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Automation
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\Automation', 'Aheadworks\Helpdesk\Model\ResourceModel\Automation');
    }
}