<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Model\Observer;

/**
 * Store Switcher Observer\StoreForm obserever
 */
class StoreForm implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageWorx\StoreSwitcher\Helper\Country
     */
    protected $helperCountry;
    
    /**
     * @var \MageWorx\GeoIP\Helper\Database
     */
    protected $geoipHelperDatabase;
        
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $sourceCountry;
    
    /**
     * @param \MageWorx\StoreSwitcher\Helper\Country $helperCountry
     * @param \MageWorx\GeoIP\Helper\Database $geoipHelperDatabase
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Directory\Model\Config\Source\Country $sourceCountry
     */
    public function __construct(
        \MageWorx\StoreSwitcher\Helper\Country $helperCountry,
        \MageWorx\GeoIP\Helper\Database $geoipHelperDatabase,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry
    ) {
    
        $this->helperCountry = $helperCountry;
        $this->geoipHelperDatabase = $geoipHelperDatabase;
        $this->coreRegistry = $coreRegistry;
        $this->sourceCountry = $sourceCountry;
    }
    
    /**
     * Adds form element on store-edit page
     *
     * @param   Magento\Framework\Event\Observer $observer
     * @return MageWorx\StoreSwitcher\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!($block = $observer->getEvent()->getBlock())) {
            return $this;
        }
        
        if (!($block instanceof \Magento\Backend\Block\System\Store\Edit\Form\Store)) {
            return $this;
        }

        if ($this->coreRegistry->registry('store_type') == 'store') {
            $storeModel = $this->coreRegistry->registry('store_data');

            $form = $block->getForm();
            $fieldset = $form->addFieldset('store_countries', array(
                'legend' => __('Store Auto Switcher')
            ));

            $storeCountries = $this->helperCountry->prepareCountryCode($storeModel->getGeoipCountryCode());
            $value = is_array($storeCountries) ? array_keys($storeCountries) : array();
            if (!$this->geoipHelperDatabase->isCityDbType()) {
                $fieldset->addField(
                    'geoip_country_code',
                    'multiselect',
                    [
                        'label' => __('Countries'),
                        'name' => 'store[geoip_country_code][]',
                        'required' => true,
                        'value' => $value,
                        'values' => $this->sourceCountry->toOptionArray(true)
                    ],
                    'store_code'
                );
            } else {
                $fieldset->addField(
                    'geoip_country_code',
                    'text',
                    [
                        'label'     => __('Locations'),
                        'name'      => 'store[geoip_country_code][]',
                        'class'     => 'requried-entry',
                    ]
                );

                $locationsBlock = $block->getLayout()->createBlock('\MageWorx\StoreSwitcher\Block\Adminhtml\Store\Edit\Tab\Locations');
                $form->getElement('geoip_country_code')->setRenderer($locationsBlock);
            }
        }

        return $this;
    }
}
