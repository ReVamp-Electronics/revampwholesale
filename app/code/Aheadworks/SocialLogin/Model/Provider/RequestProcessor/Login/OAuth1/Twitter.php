<?php
namespace Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Login\OAuth1;

use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Login;
use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Login\OAuth1;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

/**
 * Class Twitter login processor
 */
class Twitter extends OAuth1
{
    /**
     * {@inheritdoc}
     */
    public function process(ServiceInterface $service, \Magento\Framework\App\RequestInterface $request)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Twitter $service */
        $token = $service->requestRequestToken();
        $authUrl = $service->getAuthorizationUri([
            'oauth_token' => $token->getRequestToken()
        ]);
        return $this->buildRedirect($authUrl);
    }
}
