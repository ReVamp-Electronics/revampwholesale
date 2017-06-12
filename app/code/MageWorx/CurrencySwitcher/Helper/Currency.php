<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Helper;

/**
 * Currency Switcher CURRENCY helper
 */
class Currency extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \MageWorx\GeoIP\Helper\Country
     */
    protected $geoipHelperCountry;
    
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \MageWorx\GeoIP\Helper\Country $geoipHelperCountry
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \MageWorx\GeoIP\Helper\Country $geoipHelperCountry,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    
        $this->geoipHelperCountry = $geoipHelperCountry;
        $this->moduleReader = $moduleReader;
        $this->storeManager = $storeManager;
    }
    
    /**
     * Gets country-currency relations base
     *
     * @return bool|array
     */
    public function getCountryCurrency()
    {
        $path = $this->moduleReader->getModuleDir('etc', 'MageWorx_CurrencySwitcher') . '/country-currency.csv';
        
        return file_exists($path) ? file($path) : false ;
    }

    /**
     * Gets currency code by country code
     *
     * @param string $countryCode
     * @return string
     */
    public function getCurrency($countryCode)
    {
        $curBase = $this->getCountryCurrency();
        
        if ($curBase == false) {
            return null;
        }
        
        if (!count($curBase)) {
            return null;
        }
        
        $codes = $this->storeManager->getStore()->getAvailableCurrencyCodes(true);
        foreach ($curBase as $value) {
            $data = explode(';', $value);
            $curVal = trim($data[1]);
            if ($this->geoipHelperCountry->prepareCode($data[0]) != $this->geoipHelperCountry->prepareCode($countryCode)) {
                continue;
            }
            if (strstr($curVal, ',')) {
                $curCodes = explode(',', $curVal);
                if (!$curCodes) {
                    continue;
                }
                foreach ($curCodes as $code) {
                    $code = trim($code);
                    if (in_array($code, $codes)) {
                        return $code;
                    }
                }
            } else {
                if (in_array($curVal, $codes)) {
                    return $curVal;
                }
            }
        }
    }

    /**
     * Gets country codes by currency code
     *
     * @param string $currencyCode
     * @return string
     */
    public function getCountryByCurrency($currencyCode)
    {
        $curBase = $this->getCountryCurrency();
        $countries = array();
        if ($curBase !== false && count($curBase)) {
            foreach ($curBase as $value) {
                $data = explode(';', $value);
                $curVal = trim($data[1]);
                if (strpos($curVal, $currencyCode) !== false) {
                    $countries[] = trim($data[0]);
                }
            }
        }

        return implode(',', $countries);
    }
}
