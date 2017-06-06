<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Aheadworks\Helpdesk\Api\Data\DepartmentPermissionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassAssign
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class MassAssign extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket\MassAbstract
{
    const PARAM_CODE = 'agent_id';

    /**
     * {@inheritdoc}
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
            DepartmentPermissionInterface::TYPE_ASSIGN =>
                __('You do not have permission to assign tickets in this department')
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function additionalPermissionValidation($ticket)
    {
        if ($ticket->getAgentId()) {
            $adminUser = $this->permissionValidator->getAdminUser($ticket->getAgentId());
            if ($adminUser) {
                if (!$this->permissionValidator->updateValidate($ticket, $adminUser)) {
                    throw new LocalizedException(
                        __('Selected agent can not be assigned to the ticket %1', $ticket->getUid())
                    );
                }
            } else {
                throw new \Exception(__('Something went wrong'));
            }
        }
        return $this;
    }
}
