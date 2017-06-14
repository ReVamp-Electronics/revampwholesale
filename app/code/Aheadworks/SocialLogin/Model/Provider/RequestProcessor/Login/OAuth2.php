<?php
namespace Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Login;

use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Login;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

/**
 * Class OAuth2 login processor
 */
class OAuth2 extends Login
{
    /**
     * {@inheritdoc}
     */
    public function process(ServiceInterface $service, \Magento\Framework\App\RequestInterface $request)
    {
        $authUrl = $service->getAuthorizationUri();
        return $this->buildRedirect($authUrl);
    }
}
