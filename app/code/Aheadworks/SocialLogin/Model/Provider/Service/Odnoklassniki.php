<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service;

use Aheadworks\SocialLogin\Model\Provider\Service\Credentials\AdditionalCredentialsInterface;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Uri\Uri;

/**
 * Class Odnoklassniki
 */
class Odnoklassniki extends \OAuth\OAuth2\Service\AbstractService implements ServiceInterface
{
    /**
     * Defined scopes.
     */
    const SCOPE_VALUABLE_ACCESS = 'VALUABLE_ACCESS';

    /**
     * @param CredentialsInterface $credentials
     * @param ClientInterface $httpClient
     * @param TokenStorageInterface $storage
     * @param array $scopes
     * @param UriInterface|null $baseApiUri
     */
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = [],
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://api.ok.ru/api/');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        $token->setLifetime($data['expires_in']);

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires_in']);

        $token->setExtraParams($data);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://connect.ok.ru/oauth/authorize');
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://api.ok.ru/oauth/token.do');
    }

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
        $token = $this->storage->retrieveAccessToken($this->service());

        $params = array_merge(
            $params,
            ['application_key' => $this->getApplicationKey()]
        );
        $params['sig'] = $this->generateSignature(
            $params,
            $token->getAccessToken(),
            $this->credentials->getConsumerSecret()
        );

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

    /**
     * Generates a signature.
     *
     * @param array $params
     * @param string $accessToken
     * @param string $secret
     * @return string
     */
    private function generateSignature($params, $accessToken, $secret)
    {
        ksort($params);
        $paramsStr = '';
        foreach ($params as $key => $value) {
            if (in_array($key, ['sig', 'access_token'])) {
                continue;
            }
            $paramsStr .= ($key . '=' . $value);
        }
        return md5($paramsStr . md5($accessToken . $secret));
    }

    /**
     * Get application key.
     *
     * @return null|string
     */
    private function getApplicationKey()
    {
        if (!($this->credentials instanceof AdditionalCredentialsInterface)) {
            return null;
        }

        return $this->credentials->getPublicKey();
    }
}
