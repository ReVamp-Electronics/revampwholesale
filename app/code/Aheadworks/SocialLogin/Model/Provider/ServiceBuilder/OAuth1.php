<?php
namespace Aheadworks\SocialLogin\Model\Provider\ServiceBuilder;

use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Aheadworks\SocialLogin\Model\Provider\ServiceBuilder;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\OAuth1\Signature\Signature;

/**
 * OAuth1 Service builder
 */
class OAuth1 extends ServiceBuilder
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
            'signature'     => new Signature($this->credentials),
            'baseApiUri'    => $this->config->getBaseUri()
        ]);
        return $service;
    }
}
