<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\SourceAddressRepositoryInterface;
use IWD\MultiInventory\Api\Data\SourceAddressInterface;
use IWD\MultiInventory\Api\Data\SourceAddressInterfaceFactory;
use IWD\MultiInventory\Api\Data\SourceAddressSearchResultsInterface;
use IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceAddress as SourceAddressResourceModel;
use IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceAddress\CollectionFactory as SourceAddressCollectionFactory;
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
class SourceAddressRepository implements SourceAddressRepositoryInterface
{
    /**
     * @var SourceAddressResourceModel
     */
    private $resource;

    /**
     * @var SourceAddressSearchResultsInterface
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
     * @var SourceAddressCollectionFactory
     */
    private $sourceAddressCollectionFactory;

    /**
     * @var SourceAddressInterfaceFactory
     */
    private $sourceAddressFactory;

    /**
     * SourceAddressRepository constructor.
     * @param SourceAddressResourceModel $resource
     * @param SourceAddressCollectionFactory $addressCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceAddressInterfaceFactory $sourceAddressFactory
     */
    public function __construct(
        SourceAddressResourceModel $resource,
        SourceAddressCollectionFactory $addressCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TimezoneInterface $timezone,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceAddressInterfaceFactory $sourceAddressFactory
    ) {
        $this->resource = $resource;
        $this->sourceAddressCollectionFactory = $addressCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceAddressFactory = $sourceAddressFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SourceAddressInterface $address)
    {
        try {
            $this->resource->save($address);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $address = $this->sourceAddressFactory->create();
        $this->resource->load($address, $id, SourceAddressInterface::ID);
        if (!$address->getId()) {
            throw new NoSuchEntityException(__('Address with id "%1" does not exist.', $id));
        }
        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getBySourceId($sourceId)
    {
        $address = $this->sourceAddressFactory->create();
        $this->resource->load($address, $sourceId, SourceAddressInterface::STOCK_ID);

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->sourceAddressCollectionFactory->create();
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
        $addresses = [];

        /** @var SourceAddressInterface $addressModel */
        foreach ($collection as $addressModel) {
            $addresses[] = $addressModel;
        }

        $searchResults->setItems($addresses);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SourceAddressInterface $address)
    {
        try {
            $this->resource->delete($address);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
