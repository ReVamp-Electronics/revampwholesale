<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model\Observer;

/**
 * Store Switcher Observer\Countries obserever
 */
class Countries implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Prepares 'geoip_country_code' field for save
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \MageWorx\StoreSwitcher\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeModel = $observer->getEvent()->getDataObject();
        if (!$storeModel) {
            return $this;
        }

        if (!($storeModel instanceof \Magento\Store\Model\Store\Interceptor)) {
            return $this;
        }
        
        if (is_array($storeModel->getGeoipCountryCode())) {
            $codes = $storeModel->getGeoipCountryCode();
            foreach ($codes as $k => $code) {
                if (!is_array($code) && $k !== $code) {
                    $codes[$code] = $code;
                    unset($codes[$k]);
                }
            }
            $storeModel->setData('geoip_country_code', serialize($codes));
        }
        
        return $this;
    }
}
