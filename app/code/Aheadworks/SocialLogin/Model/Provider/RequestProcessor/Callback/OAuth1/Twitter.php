<?php
namespace Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Callback\OAuth1;

use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Callback;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Twitter callback request processor
 */
class Twitter extends Callback
{
    /**
     * {@inheritdoc}
     */
    protected function processRequest(ServiceInterface $service, RequestInterface $request)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Twitter $service */

        $token = $service->getStorage()->retrieveAccessToken('Twitter');

        $service->requestAccessToken(
            $request->getParam('oauth_token'),
            $request->getParam('oauth_verifier'),
            $token->getRequestTokenSecret()
        );
    }
}
