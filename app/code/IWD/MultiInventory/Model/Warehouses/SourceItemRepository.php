<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\Data\SourceItemInterface;
use IWD\MultiInventory\Api\Data\SourceItemInterfaceFactory;
use IWD\MultiInventory\Api\SourceItemRepositoryInterface;
use IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceItem\CollectionFactory as SourceItemCollectionFactory;
use IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceItem as SourceItemResourceModel;
use IWD\MultiInventory\Api\Data\SourceItemSearchResultsInterfaceFactory;
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
 * Class SourceItemRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceItemRepository implements SourceItemRepositoryInterface
{
    /**
     * @var SourceItemResourceModel
     */
    private $resource;

    /**
     * @var SourceItemCollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var SourceItemSearchResultsInterfaceFactory
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
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * SourceItemRepository constructor.
     * @param SourceItemResourceModel $resource
     * @param SourceItemCollectionFactory $sourceItemCollectionFactory
     * @param SourceItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemInterfaceFactory $sourceItemFactory
     */
    public function __construct(
        SourceItemResourceModel $resource,
        SourceItemCollectionFactory $sourceItemCollectionFactory,
        SourceItemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TimezoneInterface $timezone,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemInterfaceFactory $sourceItemFactory
    ) {
        $this->resource = $resource;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemFactory = $sourceItemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SourceItemInterface $sourceItem)
    {
        try {
            $this->resource->save($sourceItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $sourceItem;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $sourceItem = $this->sourceItemFactory->create();
        $this->resource->load($sourceItem, $id, SourceItemInterface::ITEM_ID);
        if (!$sourceItem->getItemId()) {
            throw new NoSuchEntityException(__('Source item with id "%1" does not exist.', $id));
        }
        return $sourceItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($productId, $stockId)
    {
        $this->searchCriteriaBuilder
            ->addFilter('product_id', $productId)
            ->addFilter('stock_id', $stockId);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $list = $this->getList($searchCriteria);
        if ($list->getTotalCount() > 0) {
            $items = $list->getItems();
            return current($items);
        }

        /**
         * @var $stockItem \IWD\MultiInventory\Api\Data\SourceItemInterface
         */
        $stockItem = $this->sourceItemFactory->create();
        $stockItem->setStockId($stockId);
        $stockItem->setProductId($productId);

        return $stockItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->sourceItemCollectionFactory->create();
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
        $sourceItems = [];
        /** @var SourceItemInterface $sourceItemsModel */
        foreach ($collection as $sourceItemsModel) {
            $sourceItems[] = $sourceItemsModel;
        }

        $searchResults->setItems($sourceItems);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getListForProduct($productId)
    {
        $this->searchCriteriaBuilder->addFilter('product_id', $productId);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SourceItemInterface $sourceItem)
    {
        try {
            $this->resource->delete($sourceItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
