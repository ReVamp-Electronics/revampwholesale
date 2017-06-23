<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab;

use Amasty\CustomerAttributes\Model\CustomerFormManager;
use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;
use \Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class Main extends AbstractMain
{
    /**
     * @var \Amasty\CustomerAttributes\Helper\Config $helperConfig
     */
    private $helperConfig;

    /**
     * @var \Amasty\CustomerAttributes\Model\Validation $validation
     */
    private $validation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Main constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                            $context
     * @param \Magento\Framework\Registry                                        $registry
     * @param \Magento\Framework\Data\FormFactory                                $formFactory
     * @param \Magento\Eav\Helper\Data                                           $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory                   $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker              $propertyLocker
     * @param \Amasty\CustomerAttributes\Helper\Config                           $helperConfig
     * @param \Amasty\CustomerAttributes\Model\Validation                        $validation
     * @param \Magento\Store\Model\System\Store                                  $systemStore
     * @param array                                                              $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Amasty\CustomerAttributes\Helper\Config $helperConfig,
        \Amasty\CustomerAttributes\Model\Validation $validation,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->localeDate  = $context->getLocaleDate();
        $this->validation   = $validation;
        $this->helperConfig = $helperConfig;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $eavData,
            $yesnoFactory,
            $inputTypeFactory,
            $propertyLocker,
            $data
        );
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeObject */
        $attributeObject = $this->_getAttributeObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' =>
                    [
                        'id'      => 'edit_form',
                        'action'  => $this->getData('action'),
                        'method'  => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
            ]
        );
        $form->setData('enctype', 'multipart/form-data');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Attribute Properties')
            ]
        );
        if ($attributeObject->getAttributeId()) {
            $fieldset->addField(
                'attribute_id',
                'hidden',
                [
                    'name' => 'attribute_id',
                ]
            );
        }
        $this->_addElementTypes($fieldset);

        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        $labels = $attributeObject->getFrontendLabel();
        $fieldset->addField(
            'attribute_label',
            'text',
            [
                // where 0 is admin or default store view id
                'name'     => 'frontend_label[0]',
                'label'    => __('Default label'),
                'title'    => __('Default label'),
                'required' => true,
                'value'    => is_array($labels) ? $labels[0] : $labels
            ]
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name'     => 'attribute_code',
                'label'    => __('Attribute Code'),
                'title'    => __('Attribute Code - for internal use. Must be unique with no spaces'),
                'note'     => __('For internal use. Must be unique with no spaces'),
                'class'    => 'validate-code',
                'required' => true,
            ]
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(),
                ]
            );
        } else {
            $fieldset->addField(
                'stores',
                'hidden',
                [
                    'name'  => 'stores[]',
                    'value' => $this->_storeManager->getStore()->getId()
                ]
            );
            $attributeObject->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $response = $this->dataObjectFactory->create();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_customer_attribute_types', ['response' => $response]);

        $_hiddenFields  = [];
        $_disabledTypes = [];
        foreach ($response->getTypes() as $type) {
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
            if (isset($type['disabled_types'])) {
                $_disabledTypes[$type['value']] = $type['disabled_types'];
            }
        }

        $inputTypes = $this->helperConfig->getAttributeTypes();
        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name'   => 'frontend_input',
                'label'  => __('Catalog Input Type for Store Owner'),
                'title'  => __('Catalog Input Type for Store Owner'),
                'value'  => 'text',
                'values' => $inputTypes
            ]
        );

        $fieldset->addField(
            'default_value_text',
            'text',
            [
                'name'  => 'default_value_text',
                'label' => __('Default value'),
                'title' => __('Default value'),
                'value' => $attributeObject->getDefaultValue(),
            ]
        );

        $fieldset->addField(
            'default_value_yesno',
            'select',
            [
                'name'   => 'default_value_yesno',
                'label'  => __('Default value'),
                'title'  => __('Default value'),
                'values' => $yesno,
                'value'  => $attributeObject->getDefaultValue(),
            ]
        );

        $dateFormatIso = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name'        => 'default_value_date',
                'label'       => __('Default value'),
                'title'       => __('Default value'),
                'image'       => $this->getViewFileUrl('calendar.png'),
                'value'       => $attributeObject->getDefaultValue(),
                'format'      => $dateFormatIso,
                'date_format' => $dateFormatIso
            ]
        );

        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            [
                'name'  => 'default_value_textarea',
                'label' => __('Default value'),
                'title' => __('Default value'),
                'value' => $attributeObject->getDefaultValue(),
            ]
        );

        $requiredValues   = $yesno;
        $requiredValues[] = [
            'value' => CustomerFormManager::REQUIRED_ON_FRONT,
            'label' => __('On the Frontend Only')
        ];

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name'   => 'is_required',
                'label'  => __('Values Required'),
                'title'  => __('Values Required'),
                'values' => $requiredValues,
            ]
        );

        $ordinaryValidationRules   = $this->helperConfig->getValidationRules();
        $additionalValidationRules = $this->validation->getAdditionalValidation();
        $validationRules           = array_merge($ordinaryValidationRules, $additionalValidationRules);

        $fieldset->addField(
            'frontend_class',
            'select',
            [
                'name'   => 'frontend_class',
                'label'  => __('Input Validation'),
                'title'  => __('Input Validation'),
                'values' => $validationRules
            ]
        );

        $fieldset->addField(
            'file_size',
            'text',
            [
                'name'  => 'file_size',
                'label' => __('Max File Size'),
                'title' => __('Max File Size - in Mb'),
                'note'  => __('In Mb'),
            ]
        );

        $fieldset->addField(
            'file_types',
            'text',
            [
                'name'  => 'file_types',
                'label' => __('File Types'),
                'title' => __('File Types - list comma-separated file types with no spaces, like: png,txt,jpg'),
                'note'  => __('List comma-separated file types with no spaces, like: png,txt,jpg'),
            ]
        );

        // frontend properties fieldset
        $fieldset = $form->addFieldset(
            'front_fieldset',
            [
                'legend' => __('Attribute Configuration')
            ]
        );

        $this->addFrontendPropertiesFields($fieldset, $yesno);

        $values = $this->getObjectValues($attributeObject, $form);
        $attributeObject->setData($values);
        $form->addValues($values);

        $rewriteAttributeValue = [
            'status' => [
                'is_configurable' => 0
            ]
        ];
        if ($attributeObject->getId()
            && isset($rewriteAttributeValue[$attributeObject->getAttributeCode()])
        ) {
            foreach ($rewriteAttributeValue[$attributeObject->getAttributeCode()] as $field => $value) {
                $form->getElement($field)->setValue($value);
            }
        }

        $this->setForm($form);

        return $this;
    }

    private function addFrontendPropertiesFields(&$fieldset, $yesno)
    {
        $fieldset->addField(
            'is_used_in_grid',
            'select',
            [
                'name'   => 'is_used_in_grid',
                'label'  => __('Show on the Customers Grid'),
                'title'  => __('Show on the Customers Grid'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'used_in_order_grid',
            'select',
            [
                'name'   => 'used_in_order_grid',
                'label'  => __('Show on the Orders Grid'),
                'title'  => __('Show on the Orders Grid'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'on_order_view',
            'select',
            [
                'name'   => 'on_order_view',
                'label'  => __('Show on the Order View page'),
                'title'  => __('Show on the Order View page - in the Account Information block at the Backend'),
                'note'   => __('In the Account Information block at the Backend'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'is_visible_on_front',
            'select',
            [
                'name'   => 'is_visible_on_front',
                'label'  => __('Show on the Account Information page'),
                'title'  => __('Show on the Account Information page - on the Frontend'),
                'note'   => __('On the Frontend'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'account_filled',
            'select',
            [
                'name'   => 'account_filled',
                'label'  => __('Hide if Filled'),
                'title'  => __('Hide if Filled - on the Account Information page on the Frontend'),
                'note'   => __('On the Account Information page on the Frontend'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'used_in_product_listing',
            'select',
            [
                'name'   => 'used_in_product_listing',
                'label'  => __('Show on the Billing page'),
                'title'  => __('Show on the Billing page - during Checkout'),
                'note'   => __('During Checkout'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'billing_filled',
            'select',
            [
                'name'   => 'billing_filled',
                'label'  => __('Hide if Filled'),
                'title'  => __('Hide if Filled - on the Billing page during Checkout'),
                'note'   => __('On the Billing page during Checkout'),
                'values' => $yesno
            ]
        );

        $fieldset->addField(
            'on_registration',
            'select',
            [
                'name'   => 'on_registration',
                'label'  => __('Show on the Registration page'),
                'title'  => __('Show on the Registration page'),
                'values' => $yesno,
            ]
        );

        $fieldset->addField(
            'sorting_order',
            'text',
            [
                'name'  => 'sorting_order',
                'label' => __('Sorting Order'),
                'title' => __('Sorting Order - the order to display field on Frontend'),
                'note'  => __('The order to display field on frontend'),
            ]
        );
    }

    private function getObjectValues($attributeObject, $form)
    {
        $values = $attributeObject->getData();

        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            $form->getElement('frontend_input')->setDisabled(1);
            if (array_key_exists('required_on_front', $values) && $values['required_on_front']) {
                $values['is_required'] = CustomerFormManager::REQUIRED_ON_FRONT;
            }
        }

        if (array_key_exists('type_internal', $values) && $values['type_internal'] != "") {
            $values['frontend_input'] = $values['type_internal'];
        }

        return $values;
    }

    /**
     * Return attribute object with store data
     *
     * @return Attribute
     */
    protected function _getAttributeObject()
    {
        if (null === $this->_attribute) {
            $attrObject = $this->_coreRegistry->registry('entity_attribute');
        } else {
            $attrObject = $this->_attribute;
        }

        if (!$this->_storeManager->isSingleStoreMode()) {
            if (!$attrObject->getData('store_ids')) {
                $storeCollection = $this->systemStore->getStoreCollection();
                $stores          = [];
                foreach ($storeCollection as $store) {
                    $stores[] = $store->getId();
                }
                $attrObject->setData('stores', $stores);
            } else {
                $attrObject->setData(
                    'stores',
                    explode(',', $attrObject->getData('store_ids'))
                );
            }

        }

        return $attrObject;
    }

    /**
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply'];
    }
}
