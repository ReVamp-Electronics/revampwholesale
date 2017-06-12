<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Block\System\Config;

/**
 * Viewsite block to display custom field in module settings
 */
class Viewsite extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $sourceCountry;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $sourceCountry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry,
        array $data = []
    ) {
        $this->sourceCountry = $sourceCountry;
        parent::__construct($context, $data);
    }
    
    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getAddRowButtonHtml();
    }
    
    
    /**
     * Return "View site" button html
     *
     * @param string $sku
     * @return string
     */
    protected function getAddRowButtonHtml()
    {
        $storeId = null;
        $countries = $this->sourceCountry->toOptionArray(true);
        
        $select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setId('geoip_country_select')
            ->setOptions($countries)
            ->toHtml();

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'id' => 'reset_to_default_button',
                    'label' => __('View site'),
                    'type' => 'button',
                    'style' => '',
                    'onclick' => 'javascript:viewSite(); return false;',
                ]
            )
            ->toHtml();

        if ($this->_request->getParam('store')) {
            $storeId = $this->_request->getParam('store');
        } elseif ($this->_request->getParam('website')) {
            $website = $this->_request->getParam('website');
            $store = $this->_storeManager->getWebsite($website)->getDefaultStore();
            $storeId = $store->getCode();
        } else {
            $store = $this->getAnyStoreView();
            if ($store) {
                $storeId = $store->getCode();
            }
        }

        $baseUrl = $this->_storeManager->getStore($storeId)->getBaseUrl();

        $js = "<script type=\"text/javascript\">
            function viewSite() {
                window.open('" . $baseUrl . "?geoip_country=' + jQuery('#geoip_country_select').val(), '_newtab');
            }
        </script>";

        $html = $select . $button . $js;

        return $html;
    }
    
    /**
     * Get either default or any store view
     *
     * @return \Magento\Store\Model\Store|null
     */
    protected function getAnyStoreView()
    {
        $store = $this->_storeManager->getDefaultStoreView();
        if ($store) {
            return $store;
        }
        foreach ($this->_storeManager->getStores() as $store) {
            return $store;
        }
        return null;
    }
}
