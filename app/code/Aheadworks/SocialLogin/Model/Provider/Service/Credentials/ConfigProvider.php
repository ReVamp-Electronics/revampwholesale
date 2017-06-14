<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service\Credentials;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements CredentialsInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Provider code
     * @var string
     */
    protected $providerCode;

    /**
     * Config path id
     * @var string
     */
    protected $consumerIdPath;

    /**
     * Config path secret
     * @var string
     */
    protected $consumerSecretPath;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param string $providerCode
     * @param string $consumerIdPath
     * @param string $consumerSecretPath
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        $providerCode,
        $consumerIdPath,
        $consumerSecretPath
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->providerCode = $providerCode;
        $this->consumerIdPath = $consumerIdPath;
        $this->consumerSecretPath = $consumerSecretPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallbackUrl()
    {
        return $this->urlBuilder->getUrl('social/account/callback', ['provider' => $this->providerCode]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerId()
    {
        return $this->scopeConfig->getValue($this->consumerIdPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerSecret()
    {
        return $this->scopeConfig->getValue($this->consumerSecretPath);
    }
}
