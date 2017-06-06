<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

/**
 * Class TicketFlat
 * @package Aheadworks\Helpdesk\Model
 */
class TicketFlat extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Data object processor
     *
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Ticket data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory
     */
    protected $ticketFlatDataFactory;

    /**
     * Thread collection
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection
     */
    protected $thread;

    /**
     * Automation resource model
     * @var ResourceModel\Automation
     */
    protected $automationResource;

    /**
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    protected $ticketFlatRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\TicketFlat $resource
     * @param ResourceModel\Ticket\Grid\Collection $resourceCollection
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param ResourceModel\ThreadMessage\Collection $threadMessageCollection
     * @param ResourceModel\Automation $automationResource
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\ResourceModel\TicketFlat $resource = null,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection $resourceCollection = null,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection $threadMessageCollection,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        array $data = []
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->ticketFlatDataFactory = $ticketFlatDataFactory;
        $this->thread = $threadMessageCollection;
        $this->automationResource = $automationResource;
        $this->ticketFlatRepository = $ticketFlatRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\TicketFlat');
    }

    /**
     * Retrieve ticket model with ticket data
     *
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     */
    public function getDataModel()
    {
        $ticketData = $this->getData();
        $ticketDataObject = $this->ticketFlatDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ticketDataObject,
            $ticketData,
            '\Aheadworks\Helpdesk\Api\Data\TicketFlatInterface'
        );
        $ticketDataObject->setEntityId($this->getEntityId());
        return $ticketDataObject;
    }

    /**
     * Update ticket data
     *
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface $ticket
     * @return $this
     */
    public function updateData($ticket)
    {
        $ticketData = $this->dataObjectProcessor->buildOutputDataArray(
            $ticket,
            '\Aheadworks\Helpdesk\Api\Data\TicketFlatInterface'
        );

        foreach ($ticketData as $key => $data) {
            $this->setDataUsingMethod($key, $data);
        }

        $ticketId = $ticket->getId();
        if ($ticketId) {
            $this->setId($ticketId);
        }

        return $this;
    }

    /**
     * Get thread collection
     * @return ResourceModel\ThreadMessage\Collection
     */
    public function getThread()
    {
        $this->thread->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $this->thread
            ->getTicketThread($this->getTicketId())
            ->setOrder('created_at')
        ;
        return $this->thread;
    }

    /**
     * After save. Check automation
     * @return $this
     */
    public function afterSave()
    {
        //create automations
        if ($this->isObjectNew()) {
            switch ($this->getLastReplyType()) {
                case 'customer-reply':
                    $this
                        ->automationResource
                        ->createAutomationAction(
                            \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_CUSTOMER_TICKET_VALUE,
                            $this->getTicketId()
                        );
                    break;
                case 'admin-reply':
                    $this
                        ->automationResource
                        ->createAutomationAction(
                            \Aheadworks\Helpdesk\Model\Source\Automation\Event::NEW_AGENT_TICKET_VALUE,
                            $this->getTicketId()
                        );
                    break;
            }
        } else {
            $newAgentId = $this->getAgentId();
            try {
                $oldModel = $this->ticketFlatRepository->getByTicketId($this->getTicketId());
            } catch (\Exception $e) {
                return $this;
            }
            $oldAgentId = $oldModel->getAgentId();
            if ($newAgentId != $oldAgentId) {
                $this
                    ->automationResource
                    ->createAutomationAction(
                        \Aheadworks\Helpdesk\Model\Source\Automation\Event::TICKET_ASSIGNED_VALUE,
                        $this->getTicketId()
                    );
            }
        }
        parent::afterSave();
        return $this;
    }
}