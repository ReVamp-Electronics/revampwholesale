<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Api\Data;

/**
 * Interface TicketFlatSearchResultsInterface
 * @package Aheadworks\Helpdesk\Api\Data
 */
interface TicketFlatSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get ticket list.
     *
     * @api
     * @return \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface[]
     */
    public function getItems();

    /**
     * Set ticket list.
     *
     * @api
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
