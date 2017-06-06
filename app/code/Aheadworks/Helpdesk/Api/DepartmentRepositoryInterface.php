<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Api;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface DepartmentRepositoryInterface
 * @package Aheadworks\Helpdesk\Api
 * @api
 */
interface DepartmentRepositoryInterface
{
    /**
     * Save department
     *
     * @param DepartmentInterface $department
     * @return DepartmentInterface
     * @throws LocalizedException If validation fails
     */
    public function save(DepartmentInterface $department);

    /**
     * Retrieve department
     *
     * @param int $departmentId
     * @return DepartmentInterface
     * @throws NoSuchEntityException If department does not exist
     */
    public function getById($departmentId);

    /**
     * Retrieve department
     *
     * @param string $gatewayEmail
     * @return DepartmentInterface
     * @throws NoSuchEntityException If department does not exist
     */
    public function getByGatewayEmail($gatewayEmail);

    /**
     * Retrieve default department for website
     *
     * @param int $websiteId
     * @return DepartmentInterface
     * @throws LocalizedException If default department is not set
     */
    public function getDefaultByWebsiteId($websiteId);

    /**
     * Retrieve departments matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return DepartmentSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete department
     *
     * @param DepartmentInterface $department
     * @return bool true on success
     * @throws NoSuchEntityException If department does not exist
     */
    public function delete(DepartmentInterface $department);

    /**
     * Delete department by id
     *
     * @param int $departmentId
     * @return bool true on success
     * @throws NoSuchEntityException If department does not exist
     */
    public function deleteById($departmentId);
}
