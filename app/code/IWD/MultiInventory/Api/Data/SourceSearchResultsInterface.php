<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for additional stocks search results.
 * @api
 */
interface SourceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get additional stocks list.
     *
     * @return \IWD\MultiInventory\Api\Data\SourceInterface[]
     */
    public function getItems();

    /**
     * Set additional stocks list.
     *
     * @param \IWD\MultiInventory\Api\Data\SourceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
