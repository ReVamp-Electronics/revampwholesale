<?php

namespace IWD\MultiInventory\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use IWD\MultiInventory\Api\Data\SourceInterface;

/**
 * IWD MultiInventory additional stock CRUD interface.
 * @api
 */
interface SourceRepositoryInterface
{
    /**
     * Save additional stock
     *
     * @param SourceInterface $stock
     * @return SourceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(SourceInterface $stock);

    /**
     * Retrieve additional stock
     *
     * @param int $stockId
     * @return SourceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($stockId);

    /**
     * Retrieve additional stocks matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete additional stock
     *
     * @param SourceInterface $stock
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(SourceInterface $stock);
}
