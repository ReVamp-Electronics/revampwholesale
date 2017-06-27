<?php
namespace Aheadworks\SocialLogin\Api\Data;

/**
 * Account interface
 */
interface AccountInterface
{
    /**#@+
     * Account data fields
     */
    const ACCOUNT_ID = 'account_id';
    const TYPE = 'type';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const EMAIL = 'email';
    const IMAGE_PATH = 'image_path';
    const SOCIAL_ID = 'social_id';
    const CUSTOMER_ID = 'customer_id';
    const CREATED_AT = 'created_at';
    const LAST_SIGNED_AT = 'last_signed_at';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @param string $name
     * @return $this
     */
    public function setFirstName($name);

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @param string $name
     * @return $this
     */
    public function setLastName($name);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getImagePath();

    /**
     * @param string $path
     * @return $this
     */
    public function setImagePath($path);

    /**
     * @return string
     */
    public function getSocialId();

    /**
     * @param string $socialId
     * @return $this
     */
    public function setSocialId($socialId);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer();

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getLastSignedAt();

    /**
     * @param string $lastSignedAt
     * @return $this
     */
    public function setLastSignedAt($lastSignedAt);

    /**
     * @return $this
     */
    public function updateLastSignedAt();
}
