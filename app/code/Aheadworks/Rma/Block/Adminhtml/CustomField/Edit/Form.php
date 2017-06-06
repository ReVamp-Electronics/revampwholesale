<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\CustomField\Edit;

use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class Form
 * @package Aheadworks\Rma\Block\Adminhtml\CustomField\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var array
     */
    protected $typesWithOptions = [
        Type::SELECT_VALUE,
        Type::MULTI_SELECT_VALUE
    ];

    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\Refers
     */
    protected $refersSource;

    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\Type
     */
    protected $typeSource;

    /**
     * @var \Aheadworks\Rma\Model\Source\Request\Status
     */
    protected $statusSource;

    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\EditAt
     */
    protected $editAtSource;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesno;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var Form\Element\Renderer\Options
     */
    protected $optionsRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Aheadworks\Rma\Model\Source\CustomField\Refers $refersSource
     * @param Type $typeSource
     * @param \Aheadworks\Rma\Model\Source\Request\Status $statusSource
     * @param \Aheadworks\Rma\Model\Source\CustomField\EditAt $editAtSource
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param Form\Element\Renderer\Options $optionsRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Rma\Model\Source\CustomField\Refers $refersSource,
        \Aheadworks\Rma\Model\Source\CustomField\Type $typeSource,
        \Aheadworks\Rma\Model\Source\Request\Status $statusSource,
        \Aheadworks\Rma\Model\Source\CustomField\EditAt $editAtSource,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magento\Store\Model\System\Store $systemStore,
        Form\Element\Renderer\Options $optionsRenderer,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->refersSource = $refersSource;
        $this->typeSource = $typeSource;
        $this->statusSource = $statusSource;
        $this->editAtSource = $editAtSource;
        $this->yesno = $yesno;
        $this->systemStore = $systemStore;
        $this->optionsRenderer = $optionsRenderer;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('aw_rma_custom_field');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                        'id' => 'edit_form',
                        'class' => 'aw-rma-admin-edit-form custom-field',
                        'action' => $this->getData('action'),
                        'method' => 'post'
                    ]
            ]
        );
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('custom_field_');

        $fieldSet = $form->addFieldset('general_fieldset', ['legend' => __('General Information')]);
        if ($model->getId()) {
            $fieldSet->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldNameConfig = [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => !$model->getIsSystem()
        ];
        if ($model->getIsSystem()) {
            $fieldNameConfig['readonly'] = 1;
        }
        $fieldSet->addField('name', 'text', $fieldNameConfig);

        $fieldSet->addField(
            'type',
            'select',
            [
                'name' => 'type',
                'label' => __('Field Type'),
                'title' => __('Field Type'),
                'values' => $this->typeSource->getOptions(),
                'after_element_html' => $this->getTypeSelectJs(),
                'required' => !$model->getIsSystem(),
                'disabled' => $model->getId()
            ]
        );
        if ($model->getId()) {
            $fieldSet->addField('type-hidden', 'hidden', ['name' => 'type']);
        }
        $fieldSet->addField(
            'refers',
            'select',
            [
                'name' => 'refers',
                'label' => __('Refers To'),
                'title' => __('Refers To'),
                'values' => $this->refersSource->getOptions(),
                'required' => !$model->getIsSystem(),
                'disabled' => $model->getId()
            ]
        );
        if ($model->getId()) {
            $fieldSet->addField('refers-hidden', 'hidden', ['name' => 'refers']);
        }
        $fieldSet->addField(
            'editable_admin_for_status_ids',
            'multiselect',
            [
                'name' => 'editable_admin_for_status_ids[]',
                'label' => __('Admin can edit at'),
                'title' => __('Admin can edit at'),
                'values' => $this->statusSource->toOptionArray(),
            ]
        );
        $fieldSet->addField(
            'visible_for_status_ids',
            'multiselect',
            [
                'name' => 'visible_for_status_ids[]',
                'label' => __('Customer can view at'),
                'title' => __('Customer can view at'),
                'values' => $this->statusSource->toOptionArray(),
            ]
        );
        $fieldSet->addField(
            'editable_for_status_ids',
            'multiselect',
            [
                'name' => 'editable_for_status_ids[]',
                'label' => __('Customer can edit at'),
                'title' => __('Customer can edit at'),
                'values' => $this->editAtSource->toOptionArray(),
                'disabled' => $model->getIsSystem()
            ]
        );
        if ($model->getIsSystem()) {
            foreach (array_keys($model->getEditableForStatusIds()) as $index) {
                $fieldSet->addField('editable_for_status_ids-hidden_' . $index, 'hidden', ['name' => 'editable_for_status_ids[' . $index . ']']);
            }
        }
        $fieldSet->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Required Field'),
                'title' => __('Required Field'),
                'values' => $this->yesno->toArray(),
                'required' => true
            ]
        );
        $fieldSet->addField(
            'is_display_in_label',
            'select',
            [
                'name' => 'is_display_in_label',
                'label' => __('Display at Shipping Label'),
                'title' => __('Display at Shipping Label'),
                'values' => $this->yesno->toArray(),
                'required' => !$model->getIsSystem(),
                'disabled' => $model->getIsSystem()
            ]
        );
        $fieldSet->addField(
            'website_ids',
            'multiselect',
            [
                'name' => 'website_ids',
                'label' => __('Display at Websites'),
                'title' => __('Display at Websites'),
                'values' => $this->systemStore->getWebsiteValuesForForm(),
                'value' => $model->getWebsiteIds(),
                'required' => true,
            ]
        );

        $fieldSet = $form->addFieldset('frontend_labels_fieldset', ['legend' => __('Frontend Labels')]);
        foreach ($this->_storeManager->getStores() as $store) {
            $fieldSet->addField(
                'attribute-frontend_label_' . $store->getId(),
                'Aheadworks\Rma\Block\Adminhtml\Form\Element\FrontendLabel',
                [
                    'name' => "attribute[frontend_label][" . $store->getId() . "]",
                    'store_id' => $store->getId(),
                    'required'  => true,
                ]
            );
        }

        if (in_array($model->getType(), $this->typesWithOptions)) {
            $fieldSet = $form->addFieldset('manage_options_fieldset', ['legend' => __('Manage Options (values of custom field)')]);
            $fieldSet->addField(
                'option',
                'text',
                [
                    'name' => 'option',
                    'option_values' => $model->getOption(),
                    'allow_add_option' => true,
                    'allow_disable_option' => true
                ]
            )->setRenderer($this->optionsRenderer);
        }

        $form->setValues($this->prepareFormValues($model));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $model
     * @return array
     */
    protected function prepareFormValues($model)
    {
        $formValues = $model->getData();
        if (is_array($model->getAttribute())) {
            foreach ($model->getAttribute() as $attrCode => $attrValue) {
                foreach ($attrValue as $storeId => $value) {
                    $formValues['attribute-' . $attrCode . '_' . $storeId] = $value;
                }
            }
        }
        if ($model->getId()) {
            $formValues['type-hidden'] = $model->getType();
            $formValues['refers-hidden'] = $model->getRefers();
        }
        if ($model->getIsSystem()) {
            foreach ($model->getEditableForStatusIds() as $index => $value) {
                $formValues['editable_for_status_ids-hidden_' . $index] = $value;
            }
        }
        return $formValues;
    }

    /**
     * @return string
     */
    protected function getTypeSelectJs()
    {
        $options = \Zend_Json::encode([
            'typesWithOptions' => $this->typesWithOptions
        ]);
        return <<<HTML
    <script>
        require(['jquery', 'awRmaCustomFieldTypeSelect'], function($, typeSelect){
            $(document).ready(function() {
                typeSelect({$options}, $('#custom_field_type'));
            });
        });
    </script>
HTML;
    }
}
