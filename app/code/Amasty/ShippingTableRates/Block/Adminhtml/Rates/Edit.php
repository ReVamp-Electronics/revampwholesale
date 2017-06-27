<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Block\Adminhtml\Rates;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rates';
        $this->_blockGroup = 'Amasty_ShippingTableRates';

        $objectRate = $this->_coreRegistry->registry('amtable_rate');

        parent::_construct();

        $backUrl = $this->getUrl('amstrates/methods/edit/',
            [
                'id' => $objectRate->getMethodId(),
                'tab' => 'rates_section'
            ]
        );

        $this->buttonList->add(
            'my_back',
            [
                'class' => 'back',
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $backUrl . '\')'
            ],
            0,
            0
        );

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        $deleteUrl = $this->getUrl('amstrates/rates/delete/', ['id' => $objectRate->getId()]);

        $this->buttonList->add(
            'my_delete',
            [
                'class' => 'delete',
                'label' => __('Delete'),
                'onclick' => 'deleteConfirm(\'' . __('Are you sure?') . '\',\'' . $deleteUrl . '\')'
            ],
            20,
            20
        );

        $this->buttonList->remove('back');
        $this->buttonList->remove('delete');
    }

    public function getHeaderText()
    {
        return __("Edit Rate");
    }
}
