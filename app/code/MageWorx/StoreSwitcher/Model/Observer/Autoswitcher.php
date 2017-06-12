<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model\Observer;

/**
 * Store Switcher Observer\Autoswitcher obserever
 */
class Autoswitcher implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageWorx\GeoIP\Helper\Customer
     */
    protected $geoipHelperCustomer;
    
    /**
     * @var \MageWorx\StoreSwitcher\Model\Switcher
     */
    protected $modelSwitcher;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    protected $design;


    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @param \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer
     * @param \MageWorx\StoreSwitcher\Model\Switcher $modelSwitcher
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer,
        \MageWorx\StoreSwitcher\Model\Switcher $modelSwitcher,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    
        $this->geoipHelperCustomer = $geoipHelperCustomer;
        $this->request = $request;
        $this->design = $design;
        $this->modelSwitcher = $modelSwitcher;
        $this->storeManager = $storeManager;
    }
    
    /**
     * Automatically switches store according to customer's location
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \MageWorx\StoreSwitcher\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->modelSwitcher->isAllowed()) {
            return $this;
        }

        $customerStoreCode = $this->modelSwitcher->getCustomerStoreCode();
        if (!$customerStoreCode) {
            return $this;
        }
        
        $storeCookie = $this->geoipHelperCustomer->getCookie('geoip_store_code');
        if (!$storeCookie || $this->request->getParam('geoip_country', false)) {
            return $this->doRedirect($observer, $customerStoreCode, true);
        } elseif ($requestStore = $this->request->getParam('___store', false)) {
            setcookie('geoip_store_code', $requestStore, time() + (86400 * 365));
            setcookie('store', $requestStore, time() + (86400 * 365));
        }
        
        return $this;
    }
    
    /**
     * Redirect to customer store
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @param string $customerStoreCode
     * @param boolean $changeCookie
     * @return boolean|mixed
     */
    public function doRedirect($observer, $customerStoreCode, $changeCookie = false)
    {
        $this->setDesignTheme($customerStoreCode);

        setcookie('store', $customerStoreCode);
        if ($changeCookie) {
            $this->geoipHelperCustomer->setCookie('geoip_store_code', $customerStoreCode);
        }

        $this->storeManager->setCurrentStore($customerStoreCode);
        $redirectUrl = $this->modelSwitcher->getRedirectUrl($customerStoreCode);

        if ($redirectUrl) {
            return $observer->getControllerAction()
                            ->getResponse()
                            ->setRedirect($redirectUrl);
        }
            
        return true;
    }
    
    /**
     * Set design theme to store
     *
     * @param string $customerStoreCode
     * @return void
     */
    protected function setDesignTheme($customerStoreCode)
    {
        $stores  = $this->modelSwitcher->getAvailableStores();
        $store   = $stores[$customerStoreCode];
        
        $currentTheme = $this->getDesignTheme();
        $neededTheme = $this->getDesignTheme($store);
        
        if ($currentTheme != $neededTheme) {
            $this->design->setDesignTheme($neededTheme);
        }
    }
    
    /**
     * Get store design theme
     *
     * @param \Magento\Store\Model\Store $store
     * @return integer
     */
    protected function getDesignTheme($store = null)
    {
        return $this->design->getConfigurationDesignTheme(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            ['store' => $store]
        );
    }
}
