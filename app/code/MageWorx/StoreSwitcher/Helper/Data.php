<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Store Switcher config helper data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * XML config path store switcher scope
     */
    const XML_PATH_STORE_SWITCHER_SCOPE          = 'mageworx_geoip/store_switcher/store_switcher_scope';
    
    /**
     * XML config path disable store switcher key
     */
    const XML_PATH_DISABLE_STORE_SWITCHER_KEY    = 'mageworx_geoip/store_switcher/disable_store_switcher_key';
    
    /**
     * XML config path store switcher exception urls
     */
    const XML_PATH_STORE_SWITCHER_EXCEPTION_URLS = 'mageworx_geoip/store_switcher/store_switcher_exception_urls';
    
    /**
     * XML config path auto switch country
     */
    const XML_PATH_ENABLE_AUTO_SWITCH_COUNTRY    = 'mageworx_geoip/store_switcher/enable_auto_switch_country';
    
    /**
     * XML config path ip list
     */
    const XML_PATH_IP_LIST                       = 'mageworx_geoip/store_switcher/ip_list';
    
    /**
     * XML config path user agent list
     */
    const XML_PATH_USER_AGENT_LIST               = 'mageworx_geoip/store_switcher/user_agent_list';
    
    
    /**
     * Retrive Store Auto Switcher Scope
     *
     * @param int $storeId
     * @return int
     */
    public function isWebsiteScope($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_STORE_SWITCHER_SCOPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Retrive Disable Key
     *
     * @param int $storeId
     * @return string
     */
    public function getDisableKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISABLE_STORE_SWITCHER_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Retrive Exception URLs
     *
     * @param int $storeId
     * @return string
     */
    public function getExceptionUrls($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STORE_SWITCHER_EXCEPTION_URLS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if Auto Switch Billing Country enabled
     *
     * @param int $storeId
     * @return int
     */
    public function isEnableAutoSwitchCountry($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_AUTO_SWITCH_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
       
    
    /**
     * Retrive IP List
     *
     * @param int $storeId
     * @return array
     */
    public function getIpList($storeId = null)
    {
        $configData = $this->scopeConfig->getValue(
            self::XML_PATH_IP_LIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        $result = array();
        $ipList = array_filter((array)preg_split('/\r?\n/', $configData));
        foreach ($ipList as $ip) {
            $ipParts = explode('//', $ip);
            $result[] = trim($ipParts[0]);
        }
        
        return $result;
    }
    
    /**
     * Retrive User Agent List
     *
     * @param int $storeId
     * @return array
     */
    public function getUserAgentList($storeId = null)
    {
        $configData = $this->scopeConfig->getValue(
            self::XML_PATH_USER_AGENT_LIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        $configData = array_map('trim', (array)preg_split('/\r?\n/', $configData));
        
        return array_filter($configData);
    }
}
