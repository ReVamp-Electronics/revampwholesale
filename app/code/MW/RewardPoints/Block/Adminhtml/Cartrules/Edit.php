<?php

namespace MW\RewardPoints\Block\Adminhtml\Cartrules;

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
        $this->_controller = 'adminhtml_cartrules';

        parent::_construct();

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
                ],
            ],
            10
        );
    }

    public function getHeaderText()
    {
        $cartRulesData = $this->_coreRegistry->registry('data_cart_rules');
        if ($cartRulesData && $cartRulesData->getId()) {
            return __("Edit Rule '%1'", $this->_escaper->escapeHtml($cartRulesData->getName()));
        } else {
            return __('New Rule');
        }
    }
}
