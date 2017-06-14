<?php
namespace Aheadworks\SocialLogin\Test\TestStep;

use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class SocialLoginStep
 */
class SocialLoginStep implements TestStepInterface
{
    /**
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @param CustomerAccountLogin $customerAccountLogin
     * @param string $providerName
     */
    public function __construct(
        CustomerAccountLogin $customerAccountLogin,
        $providerName
    ) {
        $this->customerAccountLogin = $customerAccountLogin;
        $this->providerName = $providerName;
    }

    /**
     * Customer login via social
     *
     * @return void
     */
    public function run()
    {
        $this->customerAccountLogin->open();
        $this->customerAccountLogin->getSocialLoginBlock()->clickLoginBy($this->providerName);
    }
}
