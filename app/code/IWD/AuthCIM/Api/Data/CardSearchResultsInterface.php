<?php

namespace IWD\AuthCIM\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for saved card search results.
 * @api
 */
interface CardSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get cards list.
     *
     * @return \IWD\AuthCIM\Api\Data\CardInterface[]
     */
    public function getItems();

    /**
     * Set cards list.
     *
     * @param \IWD\AuthCIM\Api\Data\CardInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
