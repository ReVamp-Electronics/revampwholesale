<?php
namespace Aheadworks\SocialLogin\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AbstractConfig
 */
abstract class AbstractConfig
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $pathPrefix;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $pathPrefix
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $pathPrefix = ''
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    protected function getValue(
        $path,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue($this->preparePath($path), $scopeType, $scopeCode);
    }

    /**
     * Get serialized config value
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return array
     */
    protected function getSerializedValue(
        $path,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $serializedValue = $this->getValue($path, $scopeType, $scopeCode);
        return unserialize($serializedValue);
    }

    /**
     * Is set flag
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return bool
     */
    protected function isSetFlag(
        $path,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->isSetFlag($this->preparePath($path), $scopeType, $scopeCode);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function preparePath($path)
    {
        return $this->pathPrefix . $path;
    }
}
