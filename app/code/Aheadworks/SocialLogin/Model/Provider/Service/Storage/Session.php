<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service\Storage;

use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\TokenInterface;

/**
 * Class Session storage
 */
class Session implements StorageInterface
{
    /**#@+
     * Session data keys
     */
    const SESSION_KEY_ACCESS_TOKEN = '%s_access_token';

    const SESSION_KEY_AUTHORIZATION_STATE = '%s_auth_state';

    /**#@-*/

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Session key namespace
     * @var string
     */
    protected $namespace;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param string $namespace
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        $namespace = ''
    ) {
        $this->customerSession = $customerSession;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAccessToken($service)
    {
        if (!$this->hasAccessToken($service)) {
            throw new TokenNotFoundException(__('Token not found'));
        }
        return $this->getAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $this->customerSession->setData($this->prepareKey(self::SESSION_KEY_ACCESS_TOKEN), $token);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccessToken($service)
    {
        $token = $this->getAccessToken();
        return $token !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken($service)
    {
        $this->customerSession->unsData($this->prepareKey(self::SESSION_KEY_ACCESS_TOKEN));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTokens()
    {
        return $this->clearToken('');
    }

    /**
     * Get access token from session
     * @return TokenInterface|null
     */
    protected function getAccessToken()
    {
        return $this->customerSession->getData($this->prepareKey(self::SESSION_KEY_ACCESS_TOKEN));
    }

    /**
     * {@inheritdoc}
     */
    public function storeAuthorizationState($service, $state)
    {
        $this->customerSession->setData($this->prepareKey(self::SESSION_KEY_AUTHORIZATION_STATE), $state);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizationState($service)
    {
        $state = $this->getAuthState();
        return $state !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAuthorizationState($service)
    {
        if (!$this->hasAuthorizationState($service)) {
            throw new AuthorizationStateNotFoundException(__('Auth State not found'));
        }
        return $this->getAuthState();
    }

    /**
     * {@inheritdoc}
     */
    public function clearAuthorizationState($service)
    {
        $this->customerSession->unsData($this->prepareKey(self::SESSION_KEY_AUTHORIZATION_STATE));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllAuthorizationStates()
    {
        return $this->clearAuthorizationState('');
    }

    /**
     * Get Auth state
     * @return string|null
     */
    protected function getAuthState()
    {
        return $this->customerSession->getData($this->prepareKey(self::SESSION_KEY_AUTHORIZATION_STATE));
    }

    /**
     * Prepare data keys with prefix namespace
     * @param string $key
     * @return string
     */
    protected function prepareKey($key)
    {
        return sprintf($key, $this->namespace);
    }
}
