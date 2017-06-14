<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service;

/**
 * Class Vk
 */
class Vk extends \OAuth\OAuth2\Service\Vkontakte implements ServiceInterface
{
    /**
     * Request with params.
     *
     * @param $path
     * @param array $params
     * @param string $method
     * @param null $body
     * @param array $extraHeaders
     * @return string
     * @throws \OAuth\Common\Token\Exception\ExpiredTokenException
     */
    public function requestWithParams(
        $path,
        array $params,
        $method = 'GET',
        $body = null,
        array $extraHeaders = []
    ) {
        $path = $this->preparePathWithParams($path, $params);

        return $this->request($path, $method, $body, $extraHeaders);
    }

    /**
     * Prepare path with params.
     *
     * @param string $path
     * @param array $params
     * @return string
     */
    private function preparePathWithParams($path, array $params)
    {
        return $path . '?' . http_build_query($params);
    }
}
