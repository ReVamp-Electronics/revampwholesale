<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Automation;

/**
 * Class Recurring
 * @package Aheadworks\Helpdesk\Model\Automation
 */
class Recurring extends \Magento\Framework\Model\AbstractModel
{
    const PENDING_STATUS = 'pending';
    const RUNNING_STATUS = 'running';
    const DONE_STATUS = 'done';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring');
    }
}