<?php
namespace Aheadworks\SocialLogin\Model\Provider\ServiceBuilder;

use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Aheadworks\SocialLogin\Model\Provider\ServiceBuilder;
use OAuth\Common\Http\Client\CurlClient;

/**
 * OAuth2 Service builder
 */
class OAuth2 extends ServiceBuilder
{
    /**
     * @return ServiceInterface
     */
    public function build()
    {
        /** @var ServiceInterface $service */
        $service = $this->objectManager->create($this->service, [
            'credentials'   => $this->credentials,
            'httpClient'    => new CurlClient(),
            'storage'       => $this->storage,
            'scopes'        => $this->config->getScopes(),
            'baseApiUri'    => $this->config->getBaseUri(),
            'apiVersion'    => ""
        ]);
        return $service;
    }
}
