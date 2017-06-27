<?php
namespace Aheadworks\SocialLogin\Model\Provider\Service\Credentials;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

/**
 * Class AdditionalConfigProvider.
 */
class AdditionalConfigProvider extends ConfigProvider implements AdditionalCredentialsInterface
{
    /**
     * @var string
     */
    private $publicKeyPath;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param string $providerCode
     * @param string $consumerIdPath
     * @param string $consumerSecretPath
     * @param $publicKeyPath
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        $providerCode,
        $consumerIdPath,
        $consumerSecretPath,
        $publicKeyPath
    ) {
        parent::__construct($scopeConfig, $urlBuilder, $providerCode, $consumerIdPath, $consumerSecretPath);
        $this->publicKeyPath = $publicKeyPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicKey()
    {
        return $this->scopeConfig->getValue($this->publicKeyPath);
    }
}
