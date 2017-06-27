<?php

namespace IWD\AuthCIM\Api;

use IWD\AuthCIM\Api\Data\RefundInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Authorize.net CIM deferred refunds CRUD interface.
 * @api
 */
interface RefundRepositoryInterface
{
    /**
     * Save deferred refund.
     *
     * @param RefundInterface $refund
     * @return RefundInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(RefundInterface $refund);

    /**
     * Retrieve deferred refund.
     *
     * @param string $id
     * @return RefundInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve deferred refunds matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \IWD\AuthCIM\Api\Data\RefundSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete deferred refund.
     *
     * @param RefundInterface $refund
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(RefundInterface $refund);

    /**
     * Delete deferred refund by id.
     *
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
