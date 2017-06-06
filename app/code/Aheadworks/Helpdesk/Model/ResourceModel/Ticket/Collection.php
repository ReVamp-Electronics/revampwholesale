<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Ticket;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Ticket
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
        $this->_init('Aheadworks\Helpdesk\Model\Ticket', 'Aheadworks\Helpdesk\Model\ResourceModel\Ticket');
    }

    /**
     * Join ticket flat
     * @return $this
     */
    public function joinTicketFlat()
    {
        $this
            ->getSelect()
            ->join(
                ['flat' => $this->getTable('aw_helpdesk_ticket_grid_flat')],
                'main_table.id = flat.ticket_id'
            )->columns(['total_messages' => '(flat.customer_messages + flat.agent_messages)']);

        $this->setFlag('flat_ticket_joined');

        return $this;
    }

    /**
     * Prepare collection for automation
     * @return $this
     */
    public function prepareForAutomation()
    {
        //join flat
        $this->joinTicketFlat();

        //join customer group
        $this
            ->getSelect()
            ->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'main_table.customer_id = customer.entity_id',
                ['group_id' => 'customer.group_id']
            );
        return $this;
    }

    /**
     * Add customer filter
     * @param $customerId
     * @return $this
     */
    public function addCustomerFilter($customer, $storeIds)
    {
        $filterCondition = [];
        $customerIdCondition = $this->getConnection()->prepareSqlCondition(
            'main_table.customer_id', ['eq' => $customer->getId()]
        );
        $filterCondition[] = $this->getConnection()->prepareSqlCondition(
            'main_table.customer_email', ['eq' => $customer->getEmail()]
        );
        $filterCondition[] = $this->getConnection()->prepareSqlCondition(
            'main_table.store_id', ['in' => $storeIds]
        );
        $customerEmailCondition = '(' . implode(' AND ', $filterCondition) . ')';
        $this->getSelect()->where("({$customerIdCondition} OR {$customerEmailCondition})");
        return $this;
    }

    /**
     * Order by last reply
     * @return $this
     */
    public function orderByLastReply()
    {
        if ($this->hasFlag('flat_ticket_joined')) {
            $this->setOrder('flat.last_reply_date', self::SORT_ORDER_DESC);
        }
        return $this;
    }
}