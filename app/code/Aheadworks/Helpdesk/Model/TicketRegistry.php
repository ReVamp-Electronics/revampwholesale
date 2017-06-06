<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Registry for \Aheadworks\Helpdesk\Model\Ticket
 *
 * Class TicketRegistry
 * @package Aheadworks\Helpdesk\Model
 */
class TicketRegistry
{
    /**
     * Ticket factory
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * Ticket registry by id
     * @var array
     */
    private $ticketRegistryById = [];

    /**
     * Ticket registry by Uid
     * @var array
     */
    private $ticketRegistryByUid = [];

    /**
     * Ticket resource
     * @var ResourceModel\Ticket
     */
    private $ticketResource;

    /**
     * Constructor
     *
     * @param TicketFactory $ticketFactory
     * @param ResourceModel\Ticket $ticketResource
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\TicketFactory $ticketFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket $ticketResource
    ) {
        $this->ticketResource = $ticketResource;
        $this->ticketFactory = $ticketFactory;
    }

    /**
     * Retrieve Ticket Model from registry by ID
     *
     * @param int $ticketId
     * @return Ticket
     * @throws NoSuchEntityException
     */
    public function retrieve($ticketId)
    {
        if (!isset($this->ticketRegistryById[$ticketId])) {
            /** @var Ticket $ticket */
            $ticket = $this->ticketFactory->create();
            $this->ticketResource->load($ticket, $ticketId);
            if (!$ticket->getId()) {
                throw NoSuchEntityException::singleField('ticketId', $ticketId);
            } else {
                $this->ticketRegistryById[$ticketId] = $ticket;
            }
        }
        return $this->ticketRegistryById[$ticketId];
    }

    /**
     * Retrieve Ticket Model from registry by ID
     *
     * @param int $ticketId
     * @return Ticket
     * @throws NoSuchEntityException
     */
    public function retrieveByUid($ticketUid)
    {
        if (!isset($this->ticketRegistryByUid[$ticketUid])) {
            /** @var Ticket $ticket */
            $ticket = $this->ticketFactory->create();
            $this->ticketResource->load($ticket, $ticketUid, 'uid');
            if (!$ticket->getId()) {
                throw NoSuchEntityException::singleField('ticketUid', $ticketUid);
            } else {
                $this->ticketRegistryByUid[$ticketUid] = $ticket;
            }
        }
        return $this->ticketRegistryByUid[$ticketUid];
    }

    /**
     * Remove instance of the Ticket Model from registry by ID
     *
     * @param int $ticketId
     * @return void
     */
    public function remove($ticketId)
    {
        if (isset($this->ticketRegistryById[$ticketId])) {
            unset($this->ticketRegistryById[$ticketId]);
        }
    }

    /**
     * Remove instance of the Ticket Model from registry by UID
     *
     * @param int $ticketUid
     * @return void
     */
    public function removeByUid($ticketUid)
    {
        if (isset($this->ticketRegistryByUid[$ticketUid])) {
            unset($this->ticketRegistryByUid[$ticketUid]);
        }
    }

    /**
     * Replace existing Ticket Model with a new one.
     *
     * @param Ticket $ticket
     * @return $this
     */
    public function push(Ticket $ticket)
    {
        $this->ticketRegistryById[$ticket->getId()] = $ticket;
        $this->ticketRegistryByUid[$ticket->getUid()] = $ticket;
        return $this;
    }
}
