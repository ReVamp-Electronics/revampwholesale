<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage
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
        $this->_init('Aheadworks\Helpdesk\Model\ThreadMessage', 'Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage');
    }

    /**
     * Add ticket id to filter collection
     *
     * @param $ticketId
     * @return $this
     */
    public function getTicketThread($ticketId)
    {
        $this->addFieldToFilter('ticket_id', $ticketId);
        return $this;
    }

    /**
     * Get all not internal messages
     * @return $this
     */
    public function addNotInternalType()
    {
        return $this->addFilter('type', ['neq' => \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_INTERNAL_VALUE], 'public');
    }

    /**
     * Add customer type filter
     *
     * @return $this
     */
    public function addCustomerTypeFilter()
    {
        $this->addFilter('type', ['eq' => \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE], 'public');
        return $this;
    }

    /**
     * Add agent type filter
     *
     * @return $this
     */
    public function addAgentTypeFilter()
    {
        $this->addFilter('type', ['eq' => \Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_ADMIN_VALUE], 'public');
        return $this;
    }
}