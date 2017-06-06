<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel;

use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterfaceFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory as DepartmentCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Helpdesk\Model\DepartmentRegistry;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Helpdesk\Model\Department as DepartmentModel;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;

/**
 * Class DepartmentRepository
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DepartmentRepository implements DepartmentRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DepartmentRegistry
     */
    private $departmentRegistry;

    /**
     * @var DepartmentInterfaceFactory
     */
    private $departmentFactory;

    /**
     * @var DepartmentSearchResultsInterfaceFactory
     */
    private $departmentSearchResultsFactory;

    /**
     * @var DepartmentCollectionFactory
     */
    private $departmentCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param EntityManager $entityManager
     * @param DepartmentRegistry $departmentRegistry
     * @param DepartmentInterfaceFactory $departmentFactory
     * @param DepartmentSearchResultsInterfaceFactory $departmentSearchResultsFactory
     * @param DepartmentCollectionFactory $departmentCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        EntityManager $entityManager,
        DepartmentRegistry $departmentRegistry,
        DepartmentInterfaceFactory $departmentFactory,
        DepartmentSearchResultsInterfaceFactory $departmentSearchResultsFactory,
        DepartmentCollectionFactory $departmentCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        DataObjectHelper $dataObjectHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->departmentRegistry = $departmentRegistry;
        $this->departmentFactory = $departmentFactory;
        $this->departmentSearchResultsFactory = $departmentSearchResultsFactory;
        $this->departmentCollectionFactory = $departmentCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(DepartmentInterface $department)
    {
        $department = $this->entityManager->save($department);
        $this->departmentRegistry->push($department);

        return $department;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($departmentId)
    {
        /** @var DepartmentInterface $departmentDataObject */
        $departmentDataObject = $this->departmentRegistry->retrieve($departmentId);

        if ($departmentDataObject === null) {
            $departmentDataObject = $this->departmentFactory->create();
            $this->entityManager->load($departmentDataObject, $departmentId);
            if (!$departmentDataObject->getId()) {
                throw NoSuchEntityException::singleField('departmentId', $departmentId);
            } else {
                $this->departmentRegistry->push($departmentDataObject);
            }
        }
        return $departmentDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getByGatewayEmail($gatewayEmail)
    {
        /** @var \Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterface $result */
        $result = $this->getList($this->searchCriteriaBuilder->create());

        $departmentDataObject = null;
        $departmentList = $result->getItems();
        foreach ($departmentList as $department) {
            /** @var DepartmentGatewayInterface $gateway */
            $gateway = $department->getGateway();
            if ($gateway && $gateway->getEmail() == $gatewayEmail) {
                $departmentDataObject = $department;
                break;
            }
        }
        if (!$departmentDataObject) {
            throw NoSuchEntityException::singleField('gateway_email', $gatewayEmail);
        }
        return $departmentDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultByWebsiteId($websiteId)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(DepartmentInterface::ID)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $this->searchCriteriaBuilder
            ->addFilter(DepartmentInterface::IS_DEFAULT, true)
            ->addFilter(DepartmentInterface::WEBSITE_IDS, $websiteId)
            ->addSortOrder($sortOrder);

        /** @var \Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterface $result */
        $result = $this->getList($this->searchCriteriaBuilder->create());

        if (!$result->getTotalCount()) {
            throw new LocalizedException(__('Default department for the website is not set'));
        }

        $departmentList = $result->getItems();
        /** @var DepartmentInterface $departmentDataObject */
        $departmentDataObject = array_shift($departmentList);

        return $departmentDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var DepartmentSearchResultsInterface $searchResults */
        $searchResults = $this->departmentSearchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);

        /** @var DepartmentCollection $collection */
        $collection = $this->departmentCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, DepartmentInterface::class);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == DepartmentInterface::WEBSITE_IDS) {
                    $collection->addWebsiteFilter($filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $fields[] = $filter->getField();
                    $conditions[] = [$condition => $filter->getValue()];
                }
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $departments = [];
        /** @var DepartmentModel $departmentModel */
        foreach ($collection as $departmentModel) {
            $departments[] = $this->getDepartmentDataObject($departmentModel);
        }
        $searchResults->setItems($departments);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DepartmentInterface $department)
    {
        return $this->deleteById($department->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($departmentId)
    {
        /** @var DepartmentInterface $departmentDataObject */
        $deparmentDataObject = $this->departmentRegistry->retrieve($departmentId);

        if ($deparmentDataObject === null) {
            $deparmentDataObject = $this->departmentFactory->create();
            $this->entityManager->load($deparmentDataObject, $departmentId);
        }

        if ($deparmentDataObject->getId()) {
            $this->entityManager->delete($deparmentDataObject);
        }
        $this->departmentRegistry->remove($departmentId);

        return true;
    }

    /**
     * Retrieves department data object using department model
     *
     * @param DepartmentModel $departmentModel
     * @return DepartmentInterface
     */
    private function getDepartmentDataObject($departmentModel)
    {
        /** @var DepartmentInterface $departmentDataObject */
        $departmentDataObject = $this->departmentFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $departmentDataObject,
            $departmentModel->getData(),
            DepartmentInterface::class
        );
        return $departmentDataObject;
    }
}
