<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Model\Observer;

/**
 * Currency Switcher observer
 */
class AutoSwitcher implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageWorx\GeoIP\Helper\Customer
     */
    protected $geoipHelperCustomer;
    
    /**
     * @var \MageWorx\GeoIP\Helper\Country
     */
    protected $geoipHelperCountry;
    
    /**
     * @var \MageWorx\GeoIP\Model\Geoip
     */
    protected $geoipModelGeoip;
    
    /**
     * @var \MageWorx\CurrencySwitcher\Model\Switcher
     */
    protected $modelSwitcher;
        
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @param \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer
     * @param \MageWorx\GeoIP\Helper\Country $geoipHelperCountry
     * @param \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip
     * @param \MageWorx\CurrencySwitcher\Model\Switcher $modelSwitcher
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer,
        \MageWorx\GeoIP\Helper\Country $geoipHelperCountry,
        \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip,
        \MageWorx\CurrencySwitcher\Model\Switcher $modelSwitcher,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->geoipHelperCustomer = $geoipHelperCustomer;
        $this->geoipHelperCountry = $geoipHelperCountry;
        $this->geoipModelGeoip = $geoipModelGeoip;
        $this->modelSwitcher = $modelSwitcher;
        $this->request   = $request;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }
    
    /**
     * Automatically switches currency
     *
     * @param   Magento\Framework\Event\Observer $observer
     * @return  MageWorx\CurrencySwitcher\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if (!$this->modelSwitcher->isAllowed()) {
            return $this;
        }

        $currencyCookie = $this->geoipHelperCustomer->getCookie('currency_code');
        $mageStore = $this->storeManager->getStore();
        $geoipCountry = $this->request->getParam('geoip_country');
        $currency = null;

        if ($mageStore->getCurrentCurrencyCode() == $currencyCookie && !$geoipCountry) {
            return $this;
        }

        if ($this->geoipHelperCountry->checkCountryCode($geoipCountry)) {
            $currency   = $this->modelSwitcher->getCurrency($geoipCountry);
        } elseif ($currencyCookie) {
            $currency = $currencyCookie;
        } else {
            $geoip      = $this->geoipModelGeoip->getCurrentLocation();
            $currency   = $this->modelSwitcher->getCurrency($geoip->getCode());
        }
        if ($currency && ($mageStore->getCurrentCurrencyCode() != $currency)) {
            $mageStore->setCurrentCurrencyCode($currency);

            if ($this->checkoutSession->hasQuote()) {
                $this->checkoutSession->getQuote()
                    ->collectTotals()
                    ->save();
            }
        } else {
            $this->geoipHelperCustomer->setCookie('currency_code', $mageStore->getCurrentCurrencyCode());
        }

        return $this;
    }
}
