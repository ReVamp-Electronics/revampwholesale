<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for additional stock address search results
 * @api
 */
interface SourceAddressSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get additional stocks list.
     *
     * @return \IWD\MultiInventory\Api\Data\SourceAddressInterface[]
     */
    public function getItems();

    /**
     * Set additional stocks list.
     *
     * @param \IWD\MultiInventory\Api\Data\SourceAddressInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
