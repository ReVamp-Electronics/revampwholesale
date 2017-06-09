<?php

namespace IWD\AuthCIM\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Validation
 * @package IWD\AuthCIM\Model\Config\Source
 *
 * The validationMode parameter enables you to generate a test transaction at the time you create or update
 * a customer profile. The functions createCustomerProfileRequest, createCustomerPaymentProfileRequest,
 * updateCustomerPaymentProfileRequest, and validateCustomerPaymentProfileRequest all include a
 * validationMode parameter, which can have one of the following values:
 */
class Validation implements ArrayInterface
{
    /**
     * liveMode generates a transaction to the processor in the amount of $0.01 or $0.00.
     * If successful, the transaction is immediately voided.
     * Visa authorization transactions are changing from $0.01 to $0.00 for all processors.
     * All other credit card types use $0.01.
     * Standard gateway and merchant account fees may apply to the authorization transactions.
     * For Visa transactions using $0.00, the billTo address and billTo zip fields are required.
     */
    const LIVE_MODE = 'liveMode';

    /**
     * Performs field validation only. All fields are validated.
     * However, fields with unrestricted field definitions (such as telephone number) do not generate errors.
     * If you select testMode, a $1.00 test transaction is submitted using the Luhn MOD 10 algorithm to verify
     * that the credit card number is in a valid format. This test transaction does not appear on the customer's
     * credit card statement, but it will generate and send a transaction receipt email to the merchant.
     */
    const TEST_MODE = 'testMode';

    /**
     * When this value is submitted, no additional validation is performed
     */
    const NONE = 'none';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LIVE_MODE,  'label' => __('Test transaction $0.01 (recommended)')],
            ['value' => self::TEST_MODE, 'label' => __('Card number validation only')],
            ['value' => self::NONE, 'label' => __('No validation performed')],
        ];
    }
}
