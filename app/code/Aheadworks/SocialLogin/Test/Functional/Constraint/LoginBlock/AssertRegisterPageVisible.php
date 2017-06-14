<?php
namespace Aheadworks\SocialLogin\Test\Constraint\LoginBlock;

use Magento\Customer\Test\Page\CustomerAccountCreate;

/**
 * Class AssertRegisterPageVisible
 */
class AssertRegisterPageVisible extends AssertVisible
{
    /**
     * @param CustomerAccountCreate $customerAccountCreate
     */
    public function processAssert(
        CustomerAccountCreate $customerAccountCreate
    ) {
        $this->processVisibleAssert($customerAccountCreate);
    }
}
