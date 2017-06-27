<?php
namespace Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Callback;

use Aheadworks\SocialLogin\Model\Provider\RequestProcessor\Callback;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class OAuth2 callback request processor
 */
class OAuth2 extends Callback
{
    /**
     * {@inheritdoc}
     */
    protected function processRequest(ServiceInterface $service, RequestInterface $request)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Facebook $service */

        $state = $request->getParam('state');
        $code = $request->getParam('code');

        $service->requestAccessToken($code, $state);
    }
}
