<?php

namespace IWD\AuthCIM\Model;

use IWD\AuthCIM\Api\Data\CardInterface;
use IWD\AuthCIM\Api\CardRepositoryInterface;
use IWD\AuthCIM\Api\Data\CardInterfaceFactory;
use IWD\AuthCIM\Api\Data\CardSearchResultsInterfaceFactory;
use IWD\AuthCIM\Model\ResourceModel\Card as ResourceCard;
use IWD\AuthCIM\Model\ResourceModel\Card\CollectionFactory as CardCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Class CardRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CardRepository implements CardRepositoryInterface
{
    /**
     * @var ResourceCard
     */
    private $resource;

    /**
     * @var CardFactory
     */
    private $cardFactory;

    /**
     * @var CardCollectionFactory
     */
    private $cardCollectionFactory;

    /**
     * @var CardSearchResultsInterfaceFactory
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
     * @var CardInterfaceFactory
     */
    private $dataCardFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * CardRepository constructor.
     * @param ResourceCard $resource
     * @param CardFactory $cardFactory
     * @param CardInterfaceFactory $dataCardFactory
     * @param CardCollectionFactory $cardCollectionFactory
     * @param CardSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        ResourceCard $resource,
        CardFactory $cardFactory,
        CardInterfaceFactory $dataCardFactory,
        CardCollectionFactory $cardCollectionFactory,
        CardSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TimezoneInterface $timezone,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RemoteAddress $remoteAddress
    ) {
        $this->resource = $resource;
        $this->cardFactory = $cardFactory;
        $this->cardCollectionFactory = $cardCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCardFactory = $dataCardFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CardInterface $card)
    {
        $timeNow = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
        $card->setUpdatedAt($timeNow);

        if ($this->isNewCard($card)) {
            $hash = $this->generateHash($card);
            $card->setHash($hash)
                ->setLastUseDate(null)
                ->setCreatedAt($timeNow);

            $existingCard = $this->cardFactory->create();
            $this->resource->load($existingCard, $hash, CardInterface::HASH);
            if ($existingCard->getId()) {
                $card->setId($existingCard->getId());
            }
        }

        if (!$card->getCustomerIp()) {
            $card->setCustomerIp($this->remoteAddress->getRemoteAddress());
        }

        $card->unsetAdditionalData('cc_cid');

        try {
            $this->resource->save($card);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $card;
    }

    /**
     * @param $card CardInterface
     * @return mixed
     */
    private function isNewCard(CardInterface $card)
    {
        return $card->getId() == null;
    }

    /**
     * @param CardInterface $card
     * @return string
     */
    private function generateHash(CardInterface $card)
    {
        return sha1(
            $card->getCustomerId()
            . $card->getCustomerEmail()
            . $card->getCustomerProfileId()
            . $card->getPaymentId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getByHash($hash)
    {
        $card = $this->cardFactory->create();
        $this->resource->load($card, $hash, CardInterface::HASH);
        if (!$card->getId()) {
            throw new NoSuchEntityException(__('Card with hash "%1" does not exist.', $hash));
        }
        return $card;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->cardCollectionFactory->create();
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

        $cards = [];
        /** @var Card $cardModel */
        foreach ($collection as $cardModel) {
            $cards[] = $cardModel;
        }

        $searchResults->setItems($cards);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getListForCustomer($customerId, $active = 1)
    {
        $this->searchCriteriaBuilder->addFilter(CardInterface::CUSTOMER_ID, $customerId);

        if (is_int($active)) {
            $this->searchCriteriaBuilder->addFilter(CardInterface::ACTIVE, $active);
        } elseif (is_array($active)) {
            $this->searchCriteriaBuilder->addFilter(CardInterface::ACTIVE, $active, 'in');
        }

        $criteria = $this->searchCriteriaBuilder->create();

        return $this->getList($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CardInterface $card)
    {
        try {
            $this->resource->delete($card);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByHash($hash)
    {
        return $this->delete($this->getByHash($hash));
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getSavedCcListForCustomer($customerId)
    {
        $savedCardsList = [];

        if (!empty($customerId)) {
            $list = $this->getListForCustomer($customerId);

            if ($list->getTotalCount() > 0) {
                $savedCardsList = [
                    '0' => __("New Credit Card")
                ];

                $items = $list->getItems();
                foreach ($items as $item) {
                    $savedCardsList[$item->getHash()] = $this->getCardName($item);
                }
            }
        }

        return $savedCardsList;
    }

    /**
     * @param $item \IWD\AuthCIM\Api\Data\CardInterface
     * @return string
     */
    private function getCardName($item)
    {
        $label = '';
        $additionalData = $item->getAdditionalData();

        if (is_string($additionalData) && !empty($additionalData)) {
            $additionalData = unserialize($additionalData);
        }

        if (is_array($additionalData)) {
            $ccLastNumber = isset($additionalData['cc_number']) ? $additionalData['cc_number'] : '';
            $label .= (!empty($ccLastNumber)) ? $ccLastNumber : '';

            $ccType = isset($additionalData['cc_type']) ? $additionalData['cc_type'] : '';
            $label .= (!empty($ccType)) ? ' (' . $ccType . ')' : '';
        }

        if (empty($label)) {
            $label = __('Credit Card') . ' #000' . $item->getId();
        }

        return $label;
    }
}
