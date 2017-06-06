<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring
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
        $this->_init('Aheadworks\Helpdesk\Model\Automation\Recurring', 'Aheadworks\Helpdesk\Model\ResourceModel\Automation\Recurring');
    }

    /**
     * Add ticket id filter
     * @param $ticketId
     * @return $this
     */
    public function addTicketFilter($ticketId)
    {
        $this->addFilter('ticket_id', ['eq' => $ticketId], 'public');
        return $this;
    }

    /**
     * Add action filter
     * @param $action
     * @return $this
     */
    public function addActionFilter($action)
    {
        $this->addFilter('action_type', ['eq' => $action], 'public');
        return $this;
    }

    /**
     * Add not finished status filter
     * @return $this
     */
    public function addNotFinishedFilter()
    {
        $this->addFilter('status', ['neq' => \Aheadworks\Helpdesk\Model\Automation\Recurring::DONE_STATUS], 'public');
        return $this;
    }

}