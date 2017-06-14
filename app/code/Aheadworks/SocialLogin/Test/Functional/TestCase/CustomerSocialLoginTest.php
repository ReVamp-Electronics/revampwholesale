<?php
namespace Aheadworks\SocialLogin\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. @TODO
 *
 * Steps:
 * 1. Set login block settings
 * 2. Flush cache
 * 3. Go to customer login page
 * 4. Click login via %provider%
 * 5. Submit credentials
 * 6. Go to customer account Social Accounts tab
 * 7. Assert linked account
 *
 * @group @TODO
 */
class CustomerSocialLoginTest extends Scenario
{
    /**
     * Run scenario
     */
    public function test()
    {
        $this->executeScenario();
    }
}
