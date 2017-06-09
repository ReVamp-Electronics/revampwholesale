<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\SourceRepositoryInterface;
use IWD\MultiInventory\Api\Data\SourceInterface;
use IWD\MultiInventory\Api\Data\SourceSearchResultsInterfaceFactory;
use IWD\MultiInventory\Model\ResourceModel\Warehouses\Source as SourceResourceModel;
use IWD\MultiInventory\Model\ResourceModel\Warehouses\Source\CollectionFactory as SourceCollectionFactory;
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
 * Class SourceRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceRepository implements SourceRepositoryInterface
{
    /**
     * @var SourceResourceModel
     */
    private $resource;

    /**
     * @var SourceFactory
     */
    private $sourceFactory;

    /**
     * @var SourceCollectionFactory
     */
    private $sourceCollectionFactory;

    /**
     * @var SourceSearchResultsInterfaceFactory
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
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * CardRepository constructor.
     * @param SourceResourceModel $resource
     * @param SourceFactory $sourceFactory
     * @param SourceCollectionFactory $sourceCollectionFactory
     * @param SourceSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceResourceModel $resource,
        SourceFactory $sourceFactory,
        SourceCollectionFactory $sourceCollectionFactory,
        SourceSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TimezoneInterface $timezone,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->sourceFactory = $sourceFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SourceInterface $source)
    {
        try {
            $this->resource->save($source);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        /** @var $source SourceInterface|\Magento\Framework\Model\AbstractModel */
        $source = $this->sourceFactory->create();
        $this->resource->load($source, $id, SourceInterface::STOCK_ID);
        if (!$source->getStockId()) {
            throw new NoSuchEntityException(__('Stock with hash "%1" does not exist.', $id));
        }
        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->sourceCollectionFactory->create();
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
        $sources = [];
        /** @var Source $cardModel */
        foreach ($collection as $cardModel) {
            $sources[] = $cardModel;
        }

        $searchResults->setItems($sources);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SourceInterface $source)
    {
        try {
            $this->resource->delete($source);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
