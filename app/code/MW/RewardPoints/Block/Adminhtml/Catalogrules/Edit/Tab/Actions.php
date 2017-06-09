<?php

namespace MW\RewardPoints\Block\Adminhtml\Catalogrules\Edit\Tab;

use MW\RewardPoints\Model\Typerule;

class Actions extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Actions $ruleActions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('data_catalog_rules');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Reward Points Using the Following')]
        );

        $fieldset->addField(
            'simple_action',
            'select',
            [
                'label'   => __('Apply'),
                'class'   => 'required-entry validate-digits',
                'name'    => 'simple_action',
                'options' => Typerule::getOptionArray()
            ]
        );
        $fieldset->addField(
            'reward_point',
            'text',
            [
                'label'    => __('Reward Points (X)'),
                'class'    => 'required-entry validate-digits',
                'required' => true,
                'name'     => 'reward_point',
            ]
        );
        $fieldset->addField(
            'reward_step',
            'text',
            [
                'label' => __('Per (Y) dollars Spent'),
                'class' => 'validate-digits',
                'name'  => 'reward_step',
                'note'  => __('Skip if Fixed Reward Points chosen')
            ]
        );
        $fieldset->addField(
            'stop_rules_processing',
            'select',
            [
                'label'   => __('Stop Further Rules Processing'),
                'title'   => __('Stop Further Rules Processing'),
                'name'    => 'stop_rules_processing',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No'),
                ],
                'note'    => __('Set priority under "Rule Information"')
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}

