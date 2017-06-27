<?php
namespace Aheadworks\SocialLogin\Model\Provider;

/**
 * Interface AccountInterface
 */
interface AccountInterface
{
    /**#@+
     * Account data fields
     */
    const TYPE = 'type';

    const FIRST_NAME = 'first_name';

    const LAST_NAME = 'last_name';

    const EMAIL = 'email';

    const IMAGE_URL = 'image_url';

    const SOCIAL_ID = 'social_id';
    /**#@-*/

    /**#@+
     * Account types
     */
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_TWITTER = 'twitter';
    const TYPE_GOOGLE = 'google';
    const TYPE_LINKED_IN = 'linkedin';
    const TYPE_INSTAGRAM = 'instagram';
    const TYPE_PINTEREST = 'pinterest';
    const TYPE_VK = 'vk';
    const TYPE_ODNOKLASSNIKI = 'odnoklassniki';
    const TYPE_PAYPAL = 'paypal';
    /**#@-*/

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
    public function getImageUrl();

    /**
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl);

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
     * @param array $data
     * @return $this
     */
    public function setData($data);
}
