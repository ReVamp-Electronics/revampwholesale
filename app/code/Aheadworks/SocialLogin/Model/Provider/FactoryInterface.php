<?php
namespace Aheadworks\SocialLogin\Model\Provider;

use Aheadworks\SocialLogin\Model\Config\ProviderInterface as ProviderConfig;
use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\CallbackInterface;
use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\LoginInterface;

/**
 * Interface FactoryInterface
 */
interface FactoryInterface
{
    /**
     * Create Provider service
     *
     * @return mixed
     */
    public function createService();

    /**
     * Get config
     *
     * @return ProviderConfig
     */
    public function getConfig();

    /**
     * Create callback request processor
     *
     * @return CallbackInterface
     */
    public function createCallbackRequestProcessor();

    /**
     * Create login request processor
     *
     * @return LoginInterface
     */
    public function createLoginRequestProcessor();
}
