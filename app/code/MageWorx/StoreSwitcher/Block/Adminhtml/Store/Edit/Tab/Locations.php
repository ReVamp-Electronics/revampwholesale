<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Block\Adminhtml\Store\Edit\Tab;

use Magento\Framework\Data\Form\Element\AbstractElement;
use MageWorx\GeoIP\Model\Geoip;

class Locations extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element = null;
    
    /**
     * Path to countries and regions grid template
     *
     * @var string
     */
    protected $_template = 'locations.phtml';
    
    /**
     * @var \MageWorx\StoreSwitcher\Helper\Country
     */
    protected $helperCountry;

    /**
     * Country collection
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var Geoip
     */
    protected $geoIp;

    /**
     * @param \MageWorx\StoreSwitcher\Helper\Country $helperCountry
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Geoip $geoip
     * @param array $data
     */
    public function __construct(
        \MageWorx\StoreSwitcher\Helper\Country $helperCountry,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        Geoip $geoip,
        array $data = []
    ) {
        $this->helperCountry = $helperCountry;
        $this->countryCollection = $countryCollection;
        $this->coreRegistry = $coreRegistry;
        $this->geoIp = $geoip;
        parent::__construct($context, $data);
    }
    
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
    
    /**
     * @param AbstractElement $element
     * @return $this
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return AbstractElement|null
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Return all available countries and regions
     *
     * @return array
     */
    public function getLocations()
    {
        $countries = $this->geoIp->getAvailableCountriesAndRegions();
        return $countries;
    }

    /**
     * returns countries and regions, assigned to current store
     *
     * @return mixed
     */
    public function getSavedLocations()
    {
        $store = $this->coreRegistry->registry('store_data');
        $locations = $this->helperCountry->prepareCountryCode($store->getGeoipCountryCode());
        return $locations;
    }
}
