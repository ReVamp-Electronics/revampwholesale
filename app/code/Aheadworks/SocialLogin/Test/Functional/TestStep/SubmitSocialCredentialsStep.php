<?php
namespace Aheadworks\SocialLogin\Test\TestStep;

use Aheadworks\SocialLogin\Test\Block\ProviderLoginForm;
use Aheadworks\SocialLogin\Test\Fixture\SocialCredential;
use Aheadworks\SocialLogin\Test\Page\SocialProvider\LoginPagePool;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class SubmitSocialCredentialsStep
 */
class SubmitSocialCredentialsStep implements TestStepInterface
{
    /**
     * @var LoginPagePool
     */
    protected $loginPagePool;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var SocialCredential
     */
    protected $socialCredential;

    /**
     * @param LoginPagePool $loginPagePool
     * @param SocialCredential $socialCredential
     * @param string $providerName
     */
    public function __construct(
        LoginPagePool $loginPagePool,
        SocialCredential $socialCredential,
        $providerName
    ) {
        $this->loginPagePool = $loginPagePool;
        $this->socialCredential = $socialCredential;
        $this->providerName = $providerName;
    }

    /**
     * Customer login via social
     *
     * @return void
     */
    public function run()
    {
        $page = $this->loginPagePool->getPage($this->providerName);
        $page->getLoginFormBlock()->fillCredentials($this->socialCredential->getData());
        $page->getLoginFormBlock()->clickAllow();
    }
}
