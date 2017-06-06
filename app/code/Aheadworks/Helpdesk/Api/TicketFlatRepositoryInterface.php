<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Api;

/**
 * Ticket CRUD interface.
 *
 * Interface TicketFlatRepositoryInterface
 * @package Aheadworks\Helpdesk\Api
 */
interface TicketFlatRepositoryInterface
{
    /**
     * Create ticket.
     *
     * @api
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface $ticket
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Helpdesk\Api\Data\TicketFlatInterface $ticket);

    /**
     * Retrieve flat by ticket id.
     *
     * @api
     * @param int $ticketId
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ticket with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByTicketId($ticketId);

    /**
     * Retrieve tickets which match a specified criteria.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
