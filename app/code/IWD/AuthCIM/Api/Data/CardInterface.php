<?php

namespace IWD\AuthCIM\Api\Data;

/**
 * Authorize.net CIM Saved Card Information Interface.
 * It does not save CC! Only customer profile id and payment profile id.
 * @api
 */
interface CardInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID              = 'id';
    const HASH            = 'hash';
    const CUSTOMER_ID     = 'customer_id';
    const CUSTOMER_EMAIL  = 'customer_email';
    const CUSTOMER_IP     = 'customer_ip';
    const PROFILE_ID      = 'profile_id';
    const PAYMENT_ID      = 'payment_id';
    const METHOD          = 'method';
    const ACTIVE          = 'active';
    const LAST_USE_DATE   = 'last_use';
    const EXPIRATION_DATE = 'expires';
    const ADDRESS         = 'address';
    const ADDITIONAL_DATA = 'additional';
    const CREATED_DATE    = 'created_at';
    const UPDATED_DATE    = 'updated_at';
    /**#@-*/
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Get hash
     *
     * @return string|null
     */
    public function getHash();
    
    /**
     * Get Customer Id
     *
     * @return int|null
     */
    public function getCustomerId();
    
    /**
     * Get customer email
     *
     * @return string|null
     */
    public function getCustomerEmail();
    
    /**
     * Get Customer Ip
     *
     * @return string|null
     */
    public function getCustomerIp();
    
    /**
     * Get customer profile id
     *
     * @return int|null
     */
    public function getCustomerProfileId();
    
    /**
     * Get payment id
     *
     * @return int|null
     */
    public function getPaymentId();
    
    /**
     * Get payment method name
     *
     * @return string|null
     */
    public function getMethodName();
    
    /**
     * Get is card active
     *
     * @return int|null
     */
    public function getIsActive();
    
    /**
     * Get last use date
     *
     * @return string|null
     */
    public function getLastUseDate();
    
    /**
     * Get credit card expiration date
     *
     * @param $format bool
     * @return string|null
     */
    public function getExpirationDate($format = false);
    
    /**
     * Get address string
     *
     * @return string|null
     */
    public function getAddress();
    
    /**
     * Get additional data
     *
     * @param $key
     * @return string|null
     */
    public function getAdditionalData($key = null);
    
    /**
     * Get created at date
     *
     * @return string|null
     */
    public function getCreatedAt();
    
    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set ID
     *
     * @param int $id
     * @return CardInterface
     */
    public function setId($id);

    /**
     * Set hash
     *
     * @param string $hash
     * @return CardInterface
     */
    public function setHash($hash);

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return CardInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set customer email
     *
     * @param string $email
     * @return CardInterface
     */
    public function setCustomerEmail($email);

    /**
     * Set Customer Ip
     *
     * @param string $ip
     * @return CardInterface
     */
    public function setCustomerIp($ip);

    /**
     * Set customer profile id
     *
     * @param int $customerProfileId
     * @return CardInterface
     */
    public function setCustomerProfileId($customerProfileId);

    /**
     * Set payment id
     *
     * @param int $paymentId
     * @return CardInterface
     */
    public function setPaymentId($paymentId);

    /**
     * Set payment method name
     *
     * @param string $methodName
     * @return CardInterface
     */
    public function setMethodName($methodName);

    /**
     * Set is card active
     *
     * @param int $isActive
     * @return CardInterface
     */
    public function setIsActive($isActive);

    /**
     * Set last use date
     *
     * @param string $lastUseDate
     * @return CardInterface
     */
    public function setLastUseDate($lastUseDate);

    /**
     * Set credit card expiration date
     *
     * @param string $expirationDate
     * @return CardInterface
     */
    public function setExpirationDate($expirationDate);

    /**
     * Set address string
     *
     * @param string|array $address
     * @return CardInterface
     */
    public function setAddress($address);

    /**
     * Set additional data
     *
     * @param string $additionalData
     * @return CardInterface
     */
    public function setAdditionalData($additionalData);

    /**
     * Add additional data
     *
     * @param string|array $key
     * @param string|null $value
     * @return CardInterface
     */
    public function addAdditionalData($key, $value = null);

    /**
     * Unset additional data
     *
     * @param string|array $key
     * @return CardInterface
     */
    public function unsetAdditionalData($key);

    /**
     * Set created at date
     *
     * @param string $createdAt
     * @return CardInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return CardInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get last 4 numbers from credit cart in format XXXX-1111
     *
     * @return string
     */
    public function getLastCreditCardNumber();

    /**
     * Is credit card date expired
     *
     * @return bool
     */
    public function isExpired();

    /**
     * Is credit card used for place order
     *
     * @return bool
     */
    public function isInUse();

    /**
     * Get expiration month
     *
     * @return string
     */
    public function getExpirationMonth();

    /**
     * Get expiration year
     *
     * @return string
     */
    public function getExpirationYear();

    /**
     * Get credit card type (Visa, MasterCard, American Express, ...)
     *
     * @return string
     */
    public function getCreditCardType();

    /**
     * Get credit card type (VI, MC, AE, ...)
     *
     * @return string
     */
    public function getCreditCardTypeCode();
}
