<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Model;

class Switcher
{
    /**
     * Backend area code.
     *
     * @var string
     */
    const AREA_BACKEND = 'adminhtml';
    
    /**
     * @var \MageWorx\CurrencySwitcher\Helper\Data
     */
    protected $helperData;
    
    /**
     * @var \MageWorx\CurrencySwitcher\Helper\Currency
     */
    protected $helperCurrency;
    
    /**
     * @var \MageWorx\GeoIP\Helper\Http
     */
    protected $geoipHelperHttp;
    
    /**
     * @var \MageWorx\CurrencySwitcher\Model\Relations
     */
    protected $modelRelations;
    
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @param \MageWorx\CurrencySwitcher\Helper\Data $helperData
     * @param \MageWorx\CurrencySwitcher\Helper\Currency $helperCurrency
     * @param \MageWorx\GeoIP\Helper\Http $geoipHelperHttp
     * @param \MageWorx\CurrencySwitcher\Model\Relations $modelRelations
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \MageWorx\CurrencySwitcher\Helper\Data $helperData,
        \MageWorx\CurrencySwitcher\Helper\Currency $helperCurrency,
        \MageWorx\GeoIP\Helper\Http $geoipHelperHttp,
        \MageWorx\CurrencySwitcher\Model\Relations $modelRelations,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helperData = $helperData;
        $this->helperCurrency = $helperCurrency;
        $this->geoipHelperHttp = $geoipHelperHttp;
        $this->modelRelations = $modelRelations;
        $this->appState = $appState;
        $this->request   = $request;
    }
    
    /**
     * Checks if currency auto switch is allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->appState->getAreaCode() == self::AREA_BACKEND) {
            return false;
        }

        $userAgentList = $this->helperData->getUserAgentList();
        $userAgent = $this->geoipHelperHttp->getHttpUserAgent();
        if (!empty($userAgentList) && $userAgent) {
            foreach ($userAgentList as $agent) {
                $agent = str_replace(['*', '/'], ['.*', '\/'], $agent);
                if (preg_match("/{$agent}$/i", $userAgent)) {
                    return false;
                }
            }
        }

        $request = $this->request;
        $exceptionUrls = $this->helperData->getExceptionUrls();
        if (!empty($exceptionUrls)) {
            $requestString = $request->getRequestString();
            foreach ($exceptionUrls as $url) {
                $url = str_replace('*', '.*?', $url);
                if (preg_match('!^' . $url . '$!i', $requestString)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns currency code for auto-switch
     *
     * @param string $countryCode
     * @return string
     */
    public function getCurrency($countryCode)
    {
        $currency = $this->helperCurrency->getCurrency($countryCode);

        $customCurrency = $this->modelRelations->getCountryCurrency($countryCode);
        if ($customCurrency) {
            $currency = $customCurrency;
        }

        return $currency;
    }
}
