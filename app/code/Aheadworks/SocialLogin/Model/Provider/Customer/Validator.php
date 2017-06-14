<?php
namespace Aheadworks\SocialLogin\Model\Provider\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Validator
 */
class Validator implements ValidatorInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(CustomerInterface $customer)
    {
        $errors = [];

        $errors = array_merge($errors, $this->validateRequiredFields($customer));
        $errors = array_merge($errors, $this->validateEmail($customer));

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(CustomerInterface $customer)
    {
        return !count($this->validate($customer));
    }

    /**
     * Validate email
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function validateEmail(CustomerInterface $customer)
    {
        $errors = [];

        try {
            $this->customerRepository->get($customer->getEmail());

            $errors[] = $this->buildError(
                self::ERROR_TYPE_INVALID_FIELD,
                __('Customer with this email already exist')
            );
        } catch (NoSuchEntityException $e) {
            $errors = [];
        }

        return $errors;
    }

    /**
     * Validate required field
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function validateRequiredFields(CustomerInterface $customer)
    {
        $invalidFields = [];
        if (empty($customer->getEmail())) {
            $invalidFields[] = CustomerInterface::EMAIL;
        }

        if (empty($customer->getFirstname())) {
            $invalidFields[] = CustomerInterface::FIRSTNAME;
        }

        if (empty($customer->getLastname())) {
            $invalidFields[] = CustomerInterface::LASTNAME;
        }

        $errors = [];
        foreach ($invalidFields as $field) {
            $errors[] = $this->buildError(
                self::ERROR_TYPE_EMPTY_FIELD,
                __('Empty "%1" field', $field)
            );
        }

        return $errors;
    }

    /**
     * Build error
     *
     * @param $type
     * @param $message
     * @param array $data
     * @return array
     */
    private function buildError($type, $message, array $data = [])
    {
        return [
            'type' => $type,
            'message' => $message,
            'data' => $data
        ];
    }
}
