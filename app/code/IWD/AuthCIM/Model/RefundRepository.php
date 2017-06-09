<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Api\Data\RefundInterface;
use IWD\AuthCIM\Api\RefundRepositoryInterface;
use IWD\AuthCIM\Api\Data\RefundInterfaceFactory;
use IWD\AuthCIM\Api\Data\RefundSearchResultsInterfaceFactory;
use IWD\AuthCIM\Model\ResourceModel\Refund as ResourceRefund;
use IWD\AuthCIM\Model\ResourceModel\Refund\CollectionFactory as RefundCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class RefundRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundRepository implements RefundRepositoryInterface
{
    /**
     * @var ResourceRefund
     */
    private $resource;

    /**
     * @var RefundFactory
     */
    private $refundFactory;

    /**
     * @var RefundCollectionFactory
     */
    private $refundCollectionFactory;

    /**
     * @var RefundSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var RefundInterfaceFactory
     */
    private $dataRefundFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * RefundRepository constructor.
     *
     * @param ResourceRefund $resource
     * @param RefundFactory $refundFactory
     * @param RefundInterfaceFactory $dataRefundFactory
     * @param RefundCollectionFactory $refundCollectionFactory
     * @param RefundSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ResourceRefund $resource,
        RefundFactory $refundFactory,
        RefundInterfaceFactory $dataRefundFactory,
        RefundCollectionFactory $refundCollectionFactory,
        RefundSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TimezoneInterface $timezone,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->refundFactory = $refundFactory;
        $this->refundCollectionFactory = $refundCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRefundFactory = $dataRefundFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RefundInterface $refund)
    {
        try {
            $this->resource->save($refund);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $refund;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->refundCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $refunds = [];
        /** @var Refund $refundModel */
        foreach ($collection as $refundModel) {
            $refunds[] = $refundModel;
        }
        $searchResults->setItems($refunds);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefundInterface $refund)
    {
        try {
            $this->resource->delete($refund);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $refund = $this->refundFactory->create();
        $this->resource->load($refund, $id, RefundInterface::ID);
        if (!$refund->getId()) {
            throw new NoSuchEntityException(__('Refund with id "%1" does not exist.', $id));
        }
        return $refund;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
