<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;

/**
 * Interface DepartmentSearchResultsInterface
 * @package Aheadworks\Helpdesk\Api\Data
 * @api
 */
interface DepartmentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get departments list
     *
     * @return DepartmentInterface[]
     */
    public function getItems();

    /**
     * Set departments list
     *
     * @param DepartmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
