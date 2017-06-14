<?php
namespace Aheadworks\SocialLogin\Test\Constraint\LoginBlock;

use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Class AssertLoginPageVisible
 */
class AssertLoginPageVisible extends AssertVisible
{
    /**
     * @param CustomerAccountLogin $customerAccountLogin
     */
    public function processAssert(
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->processVisibleAssert($customerAccountLogin);
    }
}
