<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Api;

/**
 * Ticket CRUD interface.
 *
 * Interface TicketRepositoryInterface
 * @package Aheadworks\Helpdesk\Api
 */
interface TicketRepositoryInterface
{
    /**
     * Create ticket.
     *
     * @api
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterface $ticket
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Helpdesk\Api\Data\TicketInterface $ticket);

    /**
     * Retrieve ticket.
     *
     * @api
     * @param int $ticketId
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ticket with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ticketId);

    /**
     * Retrieve ticket by uid.
     *
     * @api
     * @param string $uid
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ticket with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByUid($uid);

    /**
     * Retrieve tickets which match a specified criteria.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
