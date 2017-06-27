<?php

namespace IWD\AuthCIM\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use IWD\AuthCIM\Api\Data\CardInterface;

/**
 * Authorize.net CIM card CRUD interface.
 * @api
 */
interface CardRepositoryInterface
{
    /**
     * Save card.
     *
     * @param CardInterface $card
     * @return CardInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CardInterface $card);

    /**
     * Retrieve card.
     *
     * @param string $hash
     * @return CardInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByHash($hash);

    /**
     * Retrieve cards matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \IWD\AuthCIM\Api\Data\CardSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve cards matching the customer id.
     *
     * @param int $customerId
     * @param int|array $active
     * @return \IWD\AuthCIM\Api\Data\CardSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListForCustomer($customerId, $active = 1);

    /**
     * Delete card.
     *
     * @param CardInterface $card
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(CardInterface $card);

    /**
     * Delete card by hash.
     *
     * @param string $hash
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByHash($hash);
}
