<?php

namespace IWD\AuthCIM\Gateway\Config;

use IWD\AuthCIM\Model\Config\Source\SaveCC;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;

class Config extends GatewayConfig
{
    const CODE = 'iwd_authcim';

    /**#@+
     * Scope types
     */
    const KEY_MODEL = 'model';
    const KEY_TITLE = 'title';
    const KEY_PAYMENT_ACTION = 'payment_action';
    const KEY_ACTIVE = 'active';
    const KEY_IS_GATEWAY = 'is_gateway';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_USE_CVV = 'useccv';
    const KEY_ORDER_STATUS = 'order_status';
    const KEY_SANDBOX = 'sandbox';
    const KEY_VALIDATION_TYPE = 'validation_type';
    const KEY_CURRENCY = 'currency';
    const KEY_REQUIRE_CCV = 'require_ccv';
    const KEY_CC_SAVE = 'cc_save';
    const KEY_DEBUG = 'debug';
    const KEY_SEND_SHIPPING_ADDRESS = 'send_shipping_address';
    const KEY_SEND_LINE_ITEMS = 'send_line_items';
    const KEY_URL_TEST = 'url_test';
    const KEY_URL_LIVE = 'url_live';
    const KEY_API_KEY = 'api_key';
    const KEY_TRANS_KEY = 'trans_key';
    const KEY_ACCEPTJS_ENABLED = 'acceptjs_enabled';
    const KEY_ACCEPTJS_KEY = 'acceptjs_key';
    const KEY_ACCEPTJS_URL_TEST = 'acceptjs_url_test';
    const KEY_ACCEPTJS_URL_LIVE = 'acceptjs_url_live';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * @var string|null
     */
    private $pathPattern;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        $methodCode = self::CODE,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );

        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
        $this->storeManager = $storeManager;

        $this->storeManager->reinitStores();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($field, $storeId = null)
    {
        if ($this->methodCode === null || $this->pathPattern === null) {
            return null;
        }

        if ($this->isWebsiteLevel($field)) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $storeId = ($storeId == null) ? true : $storeId;
            $scopeId = $this->storeManager->getStore($storeId)->getWebsiteId();
        } else {
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeId = ($storeId == null) ? $this->storeManager->getStore(true)->getId() : $storeId;
        }

        $path = sprintf($this->pathPattern, $this->methodCode, $field);

        return $this->scopeConfig->getValue($path, $scopeType, $scopeId);
    }

    /**
     * @param $field
     * @return bool
     */
    private function isWebsiteLevel($field)
    {
        return !in_array($field, [self::KEY_TITLE]);
    }

    /**
     * Retrieve available credit card types
     * @return array
     */
    public function getAvailableCardTypes()
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Check if cvv field is enabled
     * @return boolean
     */
    public function isCvvEnabled()
    {
        return (bool) $this->getValue(self::KEY_USE_CVV);
    }

    /**
     * Get Payment configuration status
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getApiLoginId()
    {
        $apiKey = (string)$this->getValue(self::KEY_API_KEY);
        $apiKey = trim($apiKey);

        if (empty($apiKey)) {
            throw new LocalizedException(__('Empty API Login'));
        }

        return $apiKey;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getTransKey()
    {
        $transKey = (string)$this->getValue(self::KEY_TRANS_KEY);
        $transKey = trim($transKey);

        if (empty($transKey)) {
            throw new LocalizedException(__('Empty Transaction Key'));
        }

        return $transKey;
    }

    /**
     * @return bool
     */
    public function getIsSandboxAccount()
    {
        return (bool) $this->getValue(self::KEY_SANDBOX);
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->getIsSandboxAccount() ? $this->getTestGatewayUrl() : $this->getLiveGatewayUrl();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getTestGatewayUrl()
    {
        $url = (string)$this->getValue(self::KEY_URL_TEST);
        $url = trim($url);
        if (empty($url)) {
            throw new LocalizedException(__('Empty Test Gateway Url'));
        }

        return $url;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getLiveGatewayUrl()
    {
        $url = (string)$this->getValue(self::KEY_URL_LIVE);
        $url = trim($url);
        if (empty($url)) {
            throw new LocalizedException(__('Empty Live Gateway Url'));
        }

        return $url;
    }

    /**
     * @return bool
     */
    public function isSaveCreditCard()
    {
        return $this->getValue(self::KEY_CC_SAVE) == SaveCC::SAVE_ALWAYS;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * @return string
     */
    public function getValidationType()
    {
        return $this->getValue(self::KEY_VALIDATION_TYPE);
    }

    /**
     * @return bool
     */
    public function getSendLineItems()
    {
        return (bool)$this->getValue(self::KEY_SEND_LINE_ITEMS);
    }

    /**
     * @return bool
     */
    public function getSendShippingAddress()
    {
        return false; //(bool)$this->getValue(self::KEY_SEND_SHIPPING_ADDRESS);
    }

    /**
     * @return bool
     */
    public function getRequireCvv()
    {
        return (bool)$this->getValue(self::KEY_REQUIRE_CCV);
    }

    /**
     * @return bool
     */
    public function isAcceptJsEnabled()
    {
        return (bool)$this->getValue(self::KEY_ACCEPTJS_ENABLED) && $this->getAcceptJsKey() != '';
    }

    /**
     * @return string
     */
    public function getAcceptJsKey()
    {
        $key = (string)$this->getValue(self::KEY_ACCEPTJS_KEY);
        return trim($key);
    }

    /**
     * @return string
     */
    public function getAcceptJsUrl()
    {
        return $this->getIsSandboxAccount() ? $this->getAcceptJsTestUrl() : $this->getAcceptJsLiveUrl();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getAcceptJsTestUrl()
    {
        $url = (string)$this->getValue(self::KEY_ACCEPTJS_URL_TEST);
        $url = trim($url);
        if (empty($url)) {
            throw new LocalizedException(__('Empty Test Gateway Url'));
        }

        return $url;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getAcceptJsLiveUrl()
    {
        $url = (string)$this->getValue(self::KEY_ACCEPTJS_URL_LIVE);
        $url = trim($url);
        if (empty($url)) {
            throw new LocalizedException(__('Empty Live Gateway Url'));
        }

        return $url;
    }
}
