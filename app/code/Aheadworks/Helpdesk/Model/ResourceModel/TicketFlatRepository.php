<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Class TicketFlatRepository
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 */
class TicketFlatRepository implements \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
{
    /**
     * Ticket flat factory
     *
     * @var \Aheadworks\Helpdesk\Model\TicketFlatFactory
     */
    protected $ticketFlatFactory;

    /**
     * Ticket flat registry
     *
     * @var \Aheadworks\Helpdesk\Model\TicketFlatRegistry
     */
    protected $ticketFlatRegistry;

    /**
     * Search results factory
     *
     * @var \Magento\Customer\Api\Data\CustomerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data object converter
     *
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * Ticket flat resource
     * @var TicketFlat
     */
    protected $ticketFlatResource;


    /**
     * Constructor
     *
     * @param \Aheadworks\Helpdesk\Model\TicketFlatFactory $ticketFactory
     * @param \Aheadworks\Helpdesk\Model\TicketFlatRegistry $ticketRegistry
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param TicketFlat $ticketFlatResource
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\TicketFlatFactory $ticketFactory,
        \Aheadworks\Helpdesk\Model\TicketFlatRegistry $ticketRegistry,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Aheadworks\Helpdesk\Model\ResourceModel\TicketFlat $ticketFlatResource
    ) {
        $this->ticketFlatFactory = $ticketFactory;
        $this->ticketFlatRegistry = $ticketRegistry;
        $this->ticketFlatResource = $ticketFlatResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save ticket
     *
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface $ticket
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface
     */
    public function save(\Aheadworks\Helpdesk\Api\Data\TicketFlatInterface $ticket)
    {
        $ticketData = $this->extensibleDataObjectConverter->toNestedArray(
            $ticket,
            [],
            '\Aheadworks\Helpdesk\Api\Data\TicketFlatInterface'
        );

        $ticketModel = $this->ticketFlatFactory->create(['data' => $ticketData]);
        $storeId = $ticketModel->getStoreId();
        if ($storeId === null) {
            $ticketModel->setStoreId($this->storeManager->getStore()->getId());
        }

        $ticketModel->setAgentId($ticket->getData('agent_id'));
        $ticketModel->setOrderId($ticket->getData('order_id'));
        $this->ticketFlatResource->save($ticketModel);
        $this->ticketFlatRegistry->push($ticketModel);
        $ticketId = $ticketModel->getTicketId();
        $savedTicket = $this->getByTicketId($ticketId);

        return $savedTicket;
    }

    /**
     * Get ticket by Ticket ID
     *
     * @param int $ticketId
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface
     */
    public function getByTicketId($ticketId)
    {
        $ticketModel = $this->ticketFlatRegistry->retrieve($ticketId);
        return $ticketModel->getDataModel();
    }

    /**
     * Get ticket items by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->ticketFlatFactory->create()->getCollection();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];

            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $tickets = [];
        /** @var \Aheadworks\Helpdesk\Model\Ticket $ticketModel */
        foreach ($collection as $ticketModel) {
            $tickets[] = $ticketModel->getDataModel();
        }
        $searchResults->setItems($tickets);
        return $searchResults;
    }
}
