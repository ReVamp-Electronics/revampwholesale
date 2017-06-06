<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Controller\Adminhtml\Automation;

/**
 * Class MassStatus
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Automation
 */
class MassStatus extends \Aheadworks\Helpdesk\Controller\Adminhtml\Automation\MassAbstract
{
    const PARAM_CODE = 'status';

    /**
     * Get filter param
     *
     * @return string
     */
    protected function getFilterParam()
    {
        return self::PARAM_CODE;
    }
}
