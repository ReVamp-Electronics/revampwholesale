<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class TicketRepository
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TicketRepository implements \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
{
    /**
     * Ticket factory
     *
     * @var \Aheadworks\Helpdesk\Model\TicketFactory
     */
    protected $ticketFactory;

    /**
     * Ticket registry
     *
     * @var \Aheadworks\Helpdesk\Model\TicketRegistry
     */
    protected $ticketRegistry;

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
     * Ticket resource
     * @var Ticket
     */
    protected $ticketResource;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param \Aheadworks\Helpdesk\Model\TicketFactory $ticketFactory
     * @param \Aheadworks\Helpdesk\Model\TicketRegistry $ticketRegistry
     * @param \Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Ticket $ticketResource
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param StoreRepositoryInterface $storeRepositoryInterface
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\TicketFactory $ticketFactory,
        \Aheadworks\Helpdesk\Model\TicketRegistry $ticketRegistry,
        \Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket $ticketResource,
        DepartmentRepositoryInterface $departmentRepository,
        StoreRepositoryInterface $storeRepositoryInterface
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->ticketRegistry = $ticketRegistry;
        $this->ticketResource = $ticketResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->departmentRepository = $departmentRepository;
        $this->storeRepository = $storeRepositoryInterface;
    }

    /**
     * Save ticket
     *
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterface $ticket
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     */
    public function save(\Aheadworks\Helpdesk\Api\Data\TicketInterface $ticket)
    {
        $ticketData = $this->extensibleDataObjectConverter->toNestedArray(
            $ticket,
            [],
            '\Aheadworks\Helpdesk\Api\Data\TicketInterface'
        );
        $ticketModel = $this->ticketFactory->create(['data' => $ticketData]);
        $storeId = $ticketModel->getStoreId();
        if ($storeId === null) {
            $ticketModel->setStoreId($this->storeManager->getStore()->getId());
        }
        if (!$ticketModel->getDepartmentId()) {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $this->storeRepository->getById($ticketModel->getStoreId());
            $departmentDataObject = $this->departmentRepository->getDefaultByWebsiteId($store->getWebsiteId());
            $ticketModel->setDepartmentId($departmentDataObject->getId());
        }
        $ticketModel->setId($ticket->getId());
        $this->ticketResource->save($ticketModel);
        $this->ticketRegistry->push($ticketModel);
        $ticketId = $ticketModel->getId();
        $savedTicket = $this->getById($ticketId);
        return $savedTicket;
    }

    /**
     * Get ticket by ID
     *
     * @param int $ticketId
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     */
    public function getById($ticketId)
    {
        $ticketModel = $this->ticketRegistry->retrieve($ticketId);
        return $ticketModel->getDataModel();
    }

    /**
     * Get ticket by UID
     *
     * @param int $ticketUid
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     */
    public function getByUid($ticketUid)
    {
        $ticketModel = $this->ticketRegistry->retrieveByUid($ticketUid);
        return $ticketModel->getDataModel();
    }

    /**
     * Get ticket items by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterface|
     * \Magento\Customer\Api\Data\CustomerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->ticketFactory->create()->getCollection();
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
