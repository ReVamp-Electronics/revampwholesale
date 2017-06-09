<?php

namespace MW\RewardPoints\Api;

interface DataRepositoryInterface
{
    /**
     * Get Customer ID by Email
     *
     * @api
     * @param string $email
     * @param int|null $websiteId
     * @return string
     */
    public function getCustomerIdByEmail($email, $websiteId = null);

    /**
     * Get balance by Email
     *
     * @api
     * @param string $email
     * @param int|null $websiteId
     * @return string
     */
    public function getBalanceByEmail($email, $websiteId = null);

    /**
     * Update balance for a customer by customer ID
     *
     * @api
     * @param int $id
     * @param int $points
     * @param string $comment
     * @return string
     */
    public function updatePoints($id, $points, $comment);

    /**
     * Get balance by Customer ID
     *
     * @api
     * @param int $id
     * @return string
     */
    public function getBalanceById($id);

    /**
     * Get reward points of product by SKU
     *
     * @api
     * @param string $sku
     * @return string
     */
    public function getProductRewardPoints($sku);
}
