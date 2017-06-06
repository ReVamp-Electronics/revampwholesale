<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Automation;

/**
 * Class Recurring
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Automation
 */
class Recurring extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Serialized fields
     * @var array
     */
    protected $_serializableFields = ['action' => [[],[]]];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_automation_cron_schedule', 'id');
    }
}
