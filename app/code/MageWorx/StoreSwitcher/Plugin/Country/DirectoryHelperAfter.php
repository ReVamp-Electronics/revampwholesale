<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Plugin\Country;

class DirectoryHelperAfter
{
    /**
     * Checkout cart index action
     *
     * @var string
     */
    const CHECKOUT_CART_ACTION = 'checkout_cart_index';
    
    /**
     * Checkout onepage index action
     *
     * @var string
     */
    const CHECKOUT_ONEPAGE_ACTION = 'checkout_index_index';
    
    /**
     * @var \MageWorx\GeoIP\Model\Geoip
     */
    protected $geoipModelGeoip;
    
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @param \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip,
        \Magento\Framework\App\RequestInterface $request
    ) {
    
        $this->geoipModelGeoip = $geoipModelGeoip;
        $this->request = $request;
    }
    
    /**
     * Set default country
     *
     * @param \Magento\Directory\Helper\Data $subject
     * @param string $countryCode
     * @return string
     */
    public function afterGetDefaultCountry($subject, $countryCode)
    {
        
        $fullActionName = $this->request->getFullActionName();
        
        if ($fullActionName == self::CHECKOUT_CART_ACTION
            || $fullActionName == self::CHECKOUT_ONEPAGE_ACTION
        ) {
            $geoip = $this->geoipModelGeoip->getCurrentLocation();
            if ($geoip->getCode()) {
                $countryCode = $geoip->getCode();
            }
        }
        
        return $countryCode;
    }
}
