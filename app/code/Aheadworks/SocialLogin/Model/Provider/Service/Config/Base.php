<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service\Config;

/**
 * Class Base
 */
class Base implements ConfigInterface
{
    /**
     * @var array
     */
    protected $scopes;

    /**
     * @var string
     */
    protected $baseUri;


    /**
     * @param array $scopes
     * @param string|null $baseUri
     */
    public function __construct(
        $scopes = [],
        $baseUri = null
    ) {
        $this->scopes = $scopes;
        $this->baseUri = $baseUri;
    }

    /**
     * Get access scopes
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Get base uri
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }
}
