<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Model\Observer;

/**
 * Currency Switcher observer
 */
class Setter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageWorx\GeoIP\Helper\Customer
     */
    protected $geoipHelperCustomer;
        
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    protected $tagFilter;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @param \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \MageWorx\GeoIP\Helper\Customer $geoipHelperCustomer,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->geoipHelperCustomer = $geoipHelperCustomer;
        $this->tagFilter = $tagFilter;
        $this->request   = $request;
    }
    
    /**
     * Changes module's cookie "currency_code" when currency is changed manually
     *
     * @param   Magento\Framework\Event\Observer $observer
     * @return  MageWorx\CurrencySwitcher\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $currency = $this->tagFilter->filter($this->request->getParam('currency'));
        $this->geoipHelperCustomer->setCookie('currency_code', $currency);

        return $this;
    }
}
