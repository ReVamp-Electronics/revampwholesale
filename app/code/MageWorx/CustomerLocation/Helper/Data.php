<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerLocation\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Customer Location config data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config path enabled in orders
     */
    const XML_PATH_ENABLED_IN_ORDERS              = 'mageworx_geoip/customer_location/enable_orders';
    
    /**
     * XML config path inabled in online customers grid
     */
    const XML_PATH_ENABLED_IN_ONLINE_CUSTOMERS    = 'mageworx_geoip/customer_location/enable_online_customers';
        
    /**
     * Check if extension enabled for order view
     *
     * @return bool
     */
    public function isEnabledForOrders($storeId = null)
    {
        return (boolean)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_IN_ORDERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if extension enabled for "online customers" grid
     *
     * @return bool
     */
    public function isEnabledForCustomers($storeId = null)
    {
        return (boolean)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_IN_ONLINE_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

}