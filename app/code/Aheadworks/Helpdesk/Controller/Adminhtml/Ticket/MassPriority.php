<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;

/**
 * Class MassPriority
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class MassPriority extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket\MassAbstract
{
    const PARAM_CODE = 'priority';

    /**
     * Get filter param
     *
     * @return string
     */
    protected function getFilterParam()
    {
        return self::PARAM_CODE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            DepartmentPermissionInterface::TYPE_UPDATE =>
                __('You do not have permission to update tickets in this department')
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function additionalPermissionValidation($ticket)
    {
        return $this;
    }
}
