<?php

namespace MW\RewardPoints\Block\Adminhtml\Catalogrules;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	/**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_escaper = $escaper;
        parent::__construct($context, $data);
    }

	protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_catalogrules';

        parent::_construct();

        $this->buttonList->add(
            'save_apply',
            [
                'label' => __('Save and Apply'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['auto_apply' => 1]]],
                        ],
                    ]
                ]
            ]
        );
        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            10
        );
    }

    public function getHeaderText()
    {
        $catalogRulesData = $this->_coreRegistry->registry('data_catalog_rules');
        if ($catalogRulesData && $catalogRulesData->getId()) {
            return __("Edit Rule '%1'", $this->_escaper->escapeHtml($catalogRulesData->getName()));
        } else {
            return __('New Rule');
        }
    }
}
