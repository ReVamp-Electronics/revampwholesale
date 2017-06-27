<?php

namespace IWD\MultiInventory\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use IWD\MultiInventory\Api\Data\SourceItemInterface;

/**
 * Interface SourceItemRepositoryInterface
 * @api
 */
interface SourceItemRepositoryInterface
{
    /**
     * Save additional stock item
     *
     * @param SourceItemInterface $stock
     * @return SourceItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(SourceItemInterface $stock);

    /**
     * Retrieve additional stock item
     *
     * @param int $stockId
     * @return SourceItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($stockId);

    /**
     * Retrieve additional stock items matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve additional stock items matching the product id
     *
     * @param int $productId
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListForProduct($productId);

    /**
     * Retrieve additional stock items matching the product id and stock id
     *
     * @param int $productId
     * @param int $stockId
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItem($productId, $stockId);

    /**
     * Delete additional stock item
     *
     * @param SourceItemInterface $card
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(SourceItemInterface $card);
}
