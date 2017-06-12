<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model;

/**
 * Store Switcher obserever
 */
class Observer implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Prepare store data after load
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return \MageWorx\StoreSwitcher\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeModel = $observer->getEvent()->getObject();
        if (!$storeModel) {
            return $this;
        }

        if (!($storeModel instanceof \Magento\Store\Model\Store\Interceptor)) {
            return $this;
        }
        
        if (!is_array($storeModel->getGeoipCountryCode())) {
            $storeModel->setGeoipCountryCode(unserialize($storeModel->getGeoipCountryCode()));
        }
        
        return $this;
    }
}
