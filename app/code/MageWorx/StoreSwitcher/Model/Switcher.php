<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model;

/**
 * Switcher model.
 *
 * Get and return info on stores.
 */
class Switcher
{

    protected $customerStoreCode = null;

    protected $availableStores = null;
    
    /**
     * @var \MageWorx\StoreSwitcher\Helper\Data
     */
    protected $helperData;
    
    /**
     * @var \MageWorx\GeoIP\Helper\Customer
     */
    protected $geoipHelperCustomer;
    
    /**
     * @var \MageWorx\GeoIP\Helper\Http
     */
    protected $geoipHelperHttp;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    
    /**
     * @param \MageWorx\StoreSwitcher\Helper\Data $helperData
     * @param \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer
     * @param \MageWorx\GeoIP\Helper\Http $geoipHelperHttp
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \MageWorx\StoreSwitcher\Helper\Data $helperData,
        \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer,
        \MageWorx\GeoIP\Helper\Http $geoipHelperHttp,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->helperData = $helperData;
        $this->geoipHelperCustomer = $geoipHelperCustomer;
        $this->geoipHelperHttp = $geoipHelperHttp;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->url = $url;
    }
    
    /**
     * Check if store auto switch is available
     *
     * @param null $request
     * @return bool
     */
    public function isAllowed($request = null)
    {
        if (!$request) {
            $request = $this->request;
        }

        if($this->helperData->getDisableKey()){
            if ($request->getQuery('_store_switcher_') == $this->helperData->getDisableKey() ||
                $request->getCookie('_store_switcher_') == $this->helperData->getDisableKey()
            ) {
                return false;
            }
        }

        $exceptionUrls = $this->helperData->getExceptionUrls();
        if (!empty($exceptionUrls)) {
            if (!is_array($exceptionUrls)) {
                $exceptionUrls = explode('\n', $exceptionUrls);
            }
            $requestString = $request->getRequestString();
            foreach ($exceptionUrls as $url) {
                $url = str_replace('*', '.*?', $url);
                if (preg_match('!^' . $url . '$!i', $requestString)) {
                    return false;
                }
            }
        }

        $ipList = $this->helperData->getIpList();
        if (!empty($ipList)) {
            foreach ($ipList as $ip) {
                $ip = trim($ip);
                $ip = str_replace(array('*', '.'), array('\d+', '\.'), $ip);
                if (preg_match("/^{$ip}$/", $this->geoipHelperCustomer->getCustomerIp())) {
                    return false;
                }
            }
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

        return true;
    }
    
    /**
     * Return store code according to current customer location
     *
     * @return bool|null|string
     */
    public function getCustomerStoreCode()
    {
        /** @var \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip */
        $geoipModelGeoip = $this->objectManager->create('MageWorx\GeoIP\Model\Geoip');
        $geoipCountry = $this->request->getParam('geoip_country', 0);

        if (is_null($this->customerStoreCode) || $geoipCountry) {
            $stores = $this->getAvailableStores();
            if (!count($stores)) {
                return false;
            } elseif (count($stores) === 1) {
                $storeModel = current($stores);
                return $storeModel->getCode();
            }

            $helperCountry = $this->objectManager->create('MageWorx\StoreSwitcher\Helper\Country');

            $geoip = $geoipModelGeoip->getCurrentLocation();

            if (!$geoipCountry) {
                $code = $geoip->getCode();
            } else {
                $code = $geoipCountry;
            }
            $customerCountryCode = $helperCountry->prepareCode($code);

            if (empty($customerCountryCode)) {
                return false;
            }

            $region = $geoip->getRegion() ? $geoip->getRegion() : false;
            $customerStoreCode = $this->getStoreCodeByCountry($customerCountryCode, $region);

            if (!$customerStoreCode) {
                $store = reset($stores);
                $customerStoreCode = $store->getCode();
            }

            if ($geoipCountry) {
                $geoipModelGeoip->changeCurrentLocation($geoipCountry);
                return $customerStoreCode;
            }

            $this->customerStoreCode = $customerStoreCode;
        }

        return $this->customerStoreCode;
    }
    
    /**
     * Retruns all stores, available for auto-switch
     *
     * @return null
     */
    public function getAvailableStores()
    {
        if (is_null($this->availableStores)) {
            $websiteId =  $this->storeManager->getStore()->getWebsiteId();

            foreach ($this->storeManager->getStores() as $store) {
                if ($store->getIsActive() == 1) {
                    if ($this->helperData->isWebsiteScope() && $store->getWebsiteId() != $websiteId) {
                        continue;
                    }
                    $stores[$store->getCode()] = $store;
                }
            }

            $this->availableStores = $stores;
        }

        return $this->availableStores;
    }
    
    /**
     * Return store code by country code
     *
     * @param string $customerCountryCode
     * @param bool|string $customerRegion
     * @return bool|string
     */
    public function getStoreCodeByCountry($customerCountryCode, $customerRegion = false)
    {
        $helperCountry = $this->objectManager->get('MageWorx\StoreSwitcher\Helper\Country');
        $stores = $this->getAvailableStores();

        $customerStoreCode = false;
        foreach ($stores as $store) {
            $storeCountryCodes = $helperCountry->prepareCountryCode($store->getGeoipCountryCode());
            if (is_array($storeCountryCodes) && !empty($storeCountryCodes[$customerCountryCode])) {
                $storeRegions = $storeCountryCodes[$customerCountryCode];
                if (!$this->objectManager->get('MageWorx\GeoIP\Helper\Database')->isCityDbType()
                    || !$customerRegion
                    || !is_array($storeRegions)
                    || empty($storeRegions)
                    || in_array($customerRegion, $storeRegions)
                ) {
                    $customerStoreCode = $store->getCode();
                    break;
                }
            }
        }

        return $customerStoreCode;
    }
    
    /**
     * Return redirect url if it is needed
     *
     * @param string $customerStoreCode
     * @return bool|string
     */
    public function getRedirectUrl($customerStoreCode)
    {
        $stores  = $this->getAvailableStores();
        $request = $this->request;
        $store   = $stores[$customerStoreCode];
        $storeId = $store->getStoreId();

        if ($request->getFullActionName() == 'catalog_product_view' && $request->getParam('id')) {
            $redirectUrl = $this->productRepository->getById($request->getParam('id'))->getProductUrl();
        } else {
            $redirectUrl = $this->storeManager->getStore($customerStoreCode)->getBaseUrl() . ltrim($request->getRequestString(), '/');
        }

        $getParams = explode('?', $request->getRequestUri(), 2);
        if (!empty($getParams[1])) {
            $redirectUrl .= '?' . $getParams[1];
        }

        $clearCurrentUrl = str_replace(array('/index.php', 'http://', 'https://'), '', $this->url->getCurrentUrl());
        $clearRedirectUrl = str_replace(array('/index.php', 'http://', 'https://'), '', $redirectUrl);

        if ($clearCurrentUrl !== $clearRedirectUrl) {
            return $redirectUrl;
        }

        return false;
    }
}
