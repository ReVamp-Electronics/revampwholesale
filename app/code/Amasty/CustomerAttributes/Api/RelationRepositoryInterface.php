<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Api;

/**
 * Interface RelationRepositoryInterface
 *
 * @api
 */
interface RelationRepositoryInterface
{
    /**
     * @param \Amasty\CustomerAttributes\Api\Data\RelationInterface $relation
     *
     * @return \Amasty\CustomerAttributes\Api\Data\RelationInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\CustomerAttributes\Api\Data\RelationInterface $relation);

    /**
     * @param int $relationId
     *
     * @return \Amasty\CustomerAttributes\Api\Data\RelationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($relationId);

    /**
     * @param \Amasty\CustomerAttributes\Api\Data\RelationInterface $relation
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\CustomerAttributes\Api\Data\RelationInterface $relation);

    /**
     * @param int $ruleId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);
}
