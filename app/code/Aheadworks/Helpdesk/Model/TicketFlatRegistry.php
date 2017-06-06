<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for \Aheadworks\Helpdesk\Model\TicketFlat
 *
 * Class TicketFlatRegistry
 * @package Aheadworks\Helpdesk\Model
 */
class TicketFlatRegistry
{
    /**
     * Ticket flat factory
     * @var TicketFlatFactory
     */
    private $ticketFlatFactory;

    /**
     * Ticket flat registry by id
     * @var array
     */
    private $ticketFlatRegistryById = [];

    /**
     * Ticket flat resource
     * @var ResourceModel\TicketFlat
     */
    private $ticketFlatResource;

    /**
     * Constructor
     *
     * @param TicketFlatFactory $ticketFactory
     * @param ResourceModel\TicketFlat $ticketFlatResource
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\TicketFlatFactory $ticketFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\TicketFlat $ticketFlatResource
    ) {
        $this->ticketFlatFactory = $ticketFactory;
        $this->ticketFlatResource = $ticketFlatResource;
    }

    /**
     * Retrieve TicketFlat Model from registry by TICKET ID
     *
     * @param int $ticketId
     * @return TicketFlat
     * @throws NoSuchEntityException
     */
    public function retrieve($ticketId)
    {
        if (!isset($this->ticketFlatRegistryById[$ticketId])) {
            /** @var Ticket $ticket */
            $ticket = $this->ticketFlatFactory->create();
            $this->ticketFlatResource->load($ticket, $ticketId, 'ticket_id');
            if (!$ticket->getId()) {
                throw NoSuchEntityException::singleField('ticketId', $ticketId);
            } else {
                $this->ticketFlatRegistryById[$ticketId] = $ticket;
            }
        }
        return $this->ticketFlatRegistryById[$ticketId];
    }

    /**
     * Remove instance of the Ticket Model from registry by ID
     *
     * @param int $ticketId
     * @return void
     */
    public function remove($ticketId)
    {
        if (isset($this->ticketFlatRegistryById[$ticketId])) {
            unset($this->ticketFlatRegistryById[$ticketId]);
        }
    }

    /**
     * Replace existing Ticket Model with a new one.
     *
     * @param TicketFlat $ticket
     * @return $this
     */
    public function push(TicketFlat $ticket)
    {
        $this->ticketFlatRegistryById[$ticket->getTicketId()] = $ticket;
        return $this;
    }
}
