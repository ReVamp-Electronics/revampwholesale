<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Directory data helper
 */
namespace MageWorx\StoreSwitcher\Helper;

class Directory extends \Magento\Directory\Helper\Data
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
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \MageWorx\GeoIP\Model\Geoip $geoipModelGeoip
    ) {
        parent::__construct($context, $configCacheType, $countryCollection, $regCollectionFactory, $jsonHelper, $storeManager, $currencyFactory);
        $this->geoipModelGeoip = $geoipModelGeoip;
    }

    /**
     * Return default country code
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        $fullActionName = $this->_request->getFullActionName();

        if ($fullActionName == self::CHECKOUT_CART_ACTION
            || $fullActionName == self::CHECKOUT_ONEPAGE_ACTION
        ) {
            $geoip = $this->geoipModelGeoip->getCurrentLocation();
            if ($geoip->getCode()) {
                return $geoip->getCode();
            }
        }

        return parent::getDefaultCountry($store);
    }
}
