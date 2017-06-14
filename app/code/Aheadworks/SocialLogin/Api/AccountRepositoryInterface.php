<?php
namespace Aheadworks\SocialLogin\Api;

use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Account Repository Interface
 */
interface AccountRepositoryInterface
{
    /**
     * Save Account
     * @param Data\AccountInterface $account
     * @return AccountInterface
     * @throws CouldNotSaveException
     */
    public function save(AccountInterface $account);

    /**
     * Get Account by id
     * @param int $accountId
     * @return AccountInterface
     * @throws NoSuchEntityException
     */
    public function get($accountId);

    /**
     * Get Account by id
     * @param string $socialId
     * @param string $type
     * @return AccountInterface
     * @throws NoSuchEntityException
     */
    public function getBySocialId($type, $socialId);

    /**
     * Delete Account
     * @param AccountInterface $account
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(AccountInterface $account);
}
