<?php
namespace Aheadworks\SocialLogin\Model\Provider\Customer;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**#@+
     * Error types
     */
    const ERROR_TYPE_EMPTY_FIELD = 'empty_field';
    const ERROR_TYPE_INVALID_FIELD = 'invalid_field';
    /**#@-*/
    
    /**
     * Validate customer data
     *
     * @param CustomerInterface $customer
     * @return string[] invalid fields
     */
    public function validate(CustomerInterface $customer);

    /**
     * Is customer valid
     *
     * @param CustomerInterface $customer
     * @return boolean
     */
    public function isValid(CustomerInterface $customer);
}
