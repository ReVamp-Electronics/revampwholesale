<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for additional stock items search results.
 * @api
 */
interface SourceItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get additional stock items list.
     *
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface[]
     */
    public function getItems();

    /**
     * Set additional stock items list.
     *
     * @param \IWD\MultiInventory\Api\Data\SourceItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
