<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Block\Adminhtml\Currency;

/**
 * Relations block for display relations between currency and countries
 */
class Relations extends \Magento\Backend\Block\Template
{
    /**
     * Path to relations grid template
     *
     * @var string
     */
    protected $_template = 'relations/grid.phtml';
    
    /**
     * Custom currency relation data
     *
     * @var array
     */
    protected $relationsData = array();
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $sourceCountry;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Directory\Model\Config\Source\Country $sourceCountry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->sourceCountry = $sourceCountry;
        parent::__construct($context, $data);
    }
    
    /**
     * Returns Save and Refresh buttons on Relations page
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Save Currency Relations'),
                'class' => 'save primary save-currency-relations',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#currency_relations_form']],
                ]
            ]
        );
        
        $this->getToolbar()->addChild(
            'refresh_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Refresh Relations'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                    'currencyswitcher/*/refresh',
                    ['store' => $this->getRequest()->getParam('store', 0)]
                ) . '\')',
                'class' => 'refresh refresh-currency-relations'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Returns Relations page header text
     *
     * @return string
     */
    public function getHeader()
    {
        return __('Manage Currency Relations');
    }
        
    /**
     * Returns URL for save action
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('currencyswitcher/relations/save');
    }

    /**
     * Returns Custom currency relation data
     *
     * @return array
     */
    public function getCurrencyRelations()
    {
        if (!$this->relationsData) {
            $this->relationsData = $this->objectManager->create('\MageWorx\CurrencySwitcher\Model\Relations')->getCollection()->getItems();
        }
        return $this->relationsData;
    }
    
    /**
     * Returns All Countries
     *
     * @return array
     */
    public function getCountries()
    {
        return $this->sourceCountry->toOptionArray(true);
    }
}
