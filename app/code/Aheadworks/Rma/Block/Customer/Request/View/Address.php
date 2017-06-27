<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request\View;

/**
 * Class Address
 * @package Aheadworks\Rma\Block\Customer\Request\View
 */
class Address extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/request/view/address.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    protected $countryInformation;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var null|array
     */
    protected $printLabel = null;

    /**
     * @var null|array
     */
    protected $countryOptions = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->countryInformation = $countryInformation;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * @return \Aheadworks\Rma\Model\Request
     */
    public function getRequestModel()
    {
        return $this->coreRegistry->registry('aw_rma_request');
    }

    /**
     * @return int|string
     */
    public function getRequestIdentityValue()
    {
        return $this->getRequestModel()->getId();
    }

    /**
     * @return array|null
     */
    public function getPrintLabel()
    {
        if ($this->printLabel === null) {
            $this->printLabel = $this->getRequestModel()->getPrintLabel();
            $this->printLabel['street'] = explode('\n', $this->printLabel['street']);
        }
        return $this->printLabel;
    }

    /**
     * @param string $countryId
     * @return string
     */
    public function getCountryName($countryId)
    {
        return $this->countryInformation->getCountryInfo($countryId)->getFullNameLocale();
    }

    /**
     * @return string
     */
    public function getAddressJsData()
    {
        $printLabel = $this->getPrintLabel();
        return \Zend_Json::encode([
            'request_id' => $this->getRequestIdentityValue(),
            'firstname' => $printLabel['firstname'],
            'lastname' => $printLabel['lastname'],
            'street' => $printLabel['street'],
            'city' => $printLabel['city'],
            'regionId' => $printLabel['region_id'],
            'region' => $printLabel['region'],
            'countryId' => $printLabel['country_id'],
            'postcode' => $printLabel['postcode'],
            'telephone' => $printLabel['telephone'],
            'countryOptions' => $this->getCountryOptions()
        ]);
    }

    /**
     * @return array
     */
    protected function getCountryOptions()
    {
        if ($this->countryOptions === null) {
            $this->countryOptions = $this->countryCollectionFactory->create()->loadByStore()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
        }
        return $this->countryOptions;
    }

    /**
     * @return bool
     */
    public function isOptionalRegionAllowed()
    {
        return $this->_scopeConfig->getValue(
            'general/region/display_all',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * @return string
     */
    public function getRegionJson()
    {
        return $this->directoryHelper->getRegionJson();
    }

    /**
     * @return array|string
     */
    public function getCountriesWithOptionalZip()
    {
        return $this->directoryHelper->getCountriesWithOptionalZip(true);
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/saveAddress');
    }
}
