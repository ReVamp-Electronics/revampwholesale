<?php
namespace Aheadworks\SocialLogin\Model\Provider\Customer;

use Aheadworks\SocialLogin\Exception\CustomerConvertException;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface as ProviderAccountInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface ConverterInterface
 */
interface ConverterInterface
{
    /**
     * Convert provider account to customer
     *
     * @param ProviderAccountInterface $providerAccount
     * @return CustomerInterface
     * @throws CustomerConvertException
     */
    public function convert(ProviderAccountInterface $providerAccount);
}
