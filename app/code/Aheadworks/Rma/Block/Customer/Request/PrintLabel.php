<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request;

/**
 * Class PrintLabel
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class PrintLabel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/request/printlabel.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

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
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
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
     * @return array|null
     */
    public function getPrintLabel()
    {
        if ($this->printLabel === null) {
            $this->printLabel = $this->getRequestModel()->getPrintLabel();
            $street = explode('\n', $this->printLabel['street']);
            if (count($street) == 1) {
                $street[] = '';
            }
            $this->printLabel['street'] = $street;
            if (!isset($this->printLabel['additionalinfo'])) {
                $this->printLabel['additionalinfo'] = '';
            }
        }
        return $this->printLabel;
    }

    /**
     * @return array
     */
    public function getCountryOptions()
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
        return $this->getUrl('*/*/saveAndPrint');
    }
}
