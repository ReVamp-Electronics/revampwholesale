<?php

namespace IWD\AuthCIM\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for deferred refund search results.
 * @api
 */
interface RefundSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get deferred refunds list.
     *
     * @return \IWD\AuthCIM\Api\Data\RefundInterface[]
     */
    public function getItems();

    /**
     * Set deferred refunds list.
     *
     * @param \IWD\AuthCIM\Api\Data\RefundInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
