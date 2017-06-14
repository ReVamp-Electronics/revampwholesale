<?php
namespace Aheadworks\SocialLogin\Model\Customer;

use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Aheadworks\SocialLogin\Exception\InvalidCustomerException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AccountManagement
 */
class AccountManagement
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Authenticate a customer by social account
     *
     * @param AccountInterface $account
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws InvalidCustomerException
     */
    public function authenticate(AccountInterface $account)
    {
        try {
            $customer = $this->customerRepository->getById($account->getCustomerId());
        } catch (NoSuchEntityException $e) {
            throw new InvalidCustomerException(__('Invalid customer for social account.'));
        }
        return $customer;
    }
}
