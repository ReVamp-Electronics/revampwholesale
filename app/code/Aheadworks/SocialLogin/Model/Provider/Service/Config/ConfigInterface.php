<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service\Config;

/**
 * Interface ConfigInterface
 */
interface ConfigInterface
{
    /**
     * Get access scopes
     * @return array
     */
    public function getScopes();

    /**
     * Get base uri
     * @return string
     */
    public function getBaseUri();
}
