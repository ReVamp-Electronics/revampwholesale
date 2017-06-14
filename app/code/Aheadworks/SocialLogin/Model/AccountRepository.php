<?php
namespace Aheadworks\SocialLogin\Model;

use Aheadworks\SocialLogin\Api\AccountRepositoryInterface;
use Aheadworks\SocialLogin\Api\Data;
use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Aheadworks\SocialLogin\Exception\InvalidSocialAccountException;
use Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory as AccountCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @var ResourceModel\Account
     */
    protected $resource;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var AccountCollectionFactory
     */
    protected $accountCollectionFactory;

    /**
     * @param ResourceModel\Account $resource
     * @param AccountFactory $accountFactory
     * @param AccountCollectionFactory $accountCollectionFactory
     */
    public function __construct(
        ResourceModel\Account $resource,
        AccountFactory $accountFactory,
        AccountCollectionFactory $accountCollectionFactory
    ) {
        $this->resource = $resource;
        $this->accountFactory = $accountFactory;
        $this->accountCollectionFactory = $accountCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AccountInterface $account)
    {
        $this->validate($account);

        try {
            $this->resource->save($account);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function get($accountId)
    {
        /** @var AccountInterface $account */
        $account = $this->accountFactory->create();
        $this->resource->load($account, $accountId);
        if (!$account->getId()) {
            throw new NoSuchEntityException(__('Account with id "%1" does not exist.', $accountId));
        }
        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function getBySocialId($type, $socialId)
    {
        /** @var \Aheadworks\SocialLogin\Model\ResourceModel\Account\Collection $accountCollection */
        $accountCollection = $this->accountCollectionFactory->create();
        $accountCollection->addFieldToFilter(AccountInterface::TYPE, $type)
            ->addFieldToFilter(AccountInterface::SOCIAL_ID, $socialId);

        /** @var AccountInterface $account */
        $account = $accountCollection->getFirstItem();

        if (!$account->getId()) {
            throw new NoSuchEntityException(__('Account with social_id "%1" does not exist.', $socialId));
        }
        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AccountInterface $account)
    {
        try {
            $this->resource->delete($account);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Validate account
     *
     * @param AccountInterface $account
     * @return bool
     * @throws InvalidSocialAccountException
     */
    protected function validate(AccountInterface $account)
    {
        if ($this->isSocialAccountExist($account)) {
            throw new InvalidSocialAccountException(__('Social account already exists'));
        }

        return true;
    }

    /**
     * Is social account exist
     *
     * @param AccountInterface $account
     * @return bool
     */
    protected function isSocialAccountExist(AccountInterface $account)
    {
        try {
            $existAccount = $this->getBySocialId($account->getType(), $account->getSocialId());
            $isAccountExist = $existAccount->getId() !== $account->getId();
        } catch (NoSuchEntityException $e) {
            $isAccountExist = false;
        }
        return $isAccountExist;
    }
}
