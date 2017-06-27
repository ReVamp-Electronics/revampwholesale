<?php
namespace Aheadworks\SocialLogin\Test\Constraint;

use Aheadworks\SocialLogin\Test\Page\CustomerSocialAccountList;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertLoginLinkVisible
 */
class AssertSocialAccountLink extends AbstractConstraint
{
    /**
     * @param CustomerSocialAccountList $socialAccountList
     * @param string $providerName
     */
    public function processAssert(
        CustomerSocialAccountList $socialAccountList,
        $providerName
    ) {
        //@TODO refactor to waitUntil
        sleep(5);
        $socialAccountList->open();
        $isLinkedAccount = $socialAccountList->getLinkedAccountsBlock()->isAccountExist($providerName);

        \PHPUnit_Framework_Assert::assertTrue(
            $isLinkedAccount
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Social account doesn\'t linked to customer.';
    }
}
