<?php
namespace Aheadworks\SocialLogin\Model\Provider\Customer;

use Aheadworks\SocialLogin\Exception\CustomerConvertException;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface as ProviderAccountInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class Converter
 */
class Converter implements ConverterInterface
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        ValidatorInterface $validator
    ) {
        $this->customerFactory = $customerFactory;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ProviderAccountInterface $providerAccount)
    {
        $customer = $this->initCustomer()
            ->setFirstname($providerAccount->getFirstName())
            ->setLastname($providerAccount->getLastName())
            ->setEmail($providerAccount->getEmail());

        if (!$this->validator->isValid($customer)) {
            throw new CustomerConvertException(
                __('Invalid customer'),
                $this->validator->validate($customer)
            );
        }

        return $customer;
    }

    /**
     * Init customer model
     *
     * @return CustomerInterface
     */
    protected function initCustomer()
    {
        return $this->customerFactory->create();
    }
}
