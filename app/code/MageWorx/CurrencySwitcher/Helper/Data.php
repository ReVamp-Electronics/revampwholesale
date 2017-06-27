<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Currency Switcher data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config user agent list
     */
    const XML_PATH_USER_AGENT_LIST   = 'mageworx_geoip/currency_switcher/user_agent_list';
    
    /**
     * XML config path list of exception urls
     */
    const XML_PATH_EXCEPTION_URLS    = 'mageworx_geoip/currency_switcher/exception_urls';
    
    /**
     * Retrive user agent list
     *
     * @param int $storeId
     * @return array
     */
    public function getUserAgentList($storeId = null)
    {
        $data = $this->scopeConfig->getValue(
            self::XML_PATH_USER_AGENT_LIST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        $data = array_map('trim', (array)preg_split('/\r?\n/', $data));
                
        return array_filter($data);
    }

    /**
     * Retrive list of exception urls
     *
     * @param int $storeId
     * @return array
     */
    public function getExceptionUrls($storeId = null)
    {
        $data = $this->scopeConfig->getValue(
            self::XML_PATH_EXCEPTION_URLS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        $data = array_map('trim', (array)preg_split('/\r?\n/', $data));
                
        return array_filter($data);
    }
}
