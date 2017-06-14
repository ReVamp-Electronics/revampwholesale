<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account;

use Aheadworks\SocialLogin\Model\Provider\AccountInterface as ProviderAccountInterface;
use Aheadworks\SocialLogin\Api\Data\AccountInterface;

/**
 * Interface ConverterInterface
 */
interface ConverterInterface
{
    /**
     * Convert provider account to social account
     *
     * @param ProviderAccountInterface $providerAccount
     * @return AccountInterface
     */
    public function convert(ProviderAccountInterface $providerAccount);
}
