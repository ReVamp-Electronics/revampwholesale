<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service\Credentials;

/**
 * Interface AdditionalCredentialsInterface
 */
interface AdditionalCredentialsInterface extends CredentialsInterface
{
    /**
     * Get application public key.
     *
     * @return string
     */
    public function getPublicKey();
}
