<?php

namespace IWD\MultiInventory\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use IWD\MultiInventory\Api\Data\SourceAddressInterface;

/**
 * IWD MultiInventory additional stock address CRUD interface.
 * @api
 */
interface SourceAddressRepositoryInterface
{
    /**
     * Save additional stock address
     *
     * @param SourceAddressInterface $address
     * @return SourceAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(SourceAddressInterface $address);

    /**
     * Retrieve additional stock address for address id
     *
     * @param int $addressId
     * @return SourceAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($addressId);

    /**
     * Retrieve additional stock address for stock
     *
     * @param int $sourceId
     * @return SourceAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySourceId($sourceId);

    /**
     * Retrieve additional stock address matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete additional stock address
     *
     * @param SourceAddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(SourceAddressInterface $address);
}
