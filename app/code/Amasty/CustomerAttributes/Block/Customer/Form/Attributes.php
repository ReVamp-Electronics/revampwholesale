<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Block\Customer\Form;

use Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\CollectionFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\Element\Factory;

class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var  array
     */
    private $_customerData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $relationCollectionFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Prepare attributes form
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Amasty\CustomerAttributes\Block\Widget\Form\Renderer\Fieldset $fieldsetRenderer
     * @param \Amasty\CustomerAttributes\Block\Widget\Form\Renderer\Element $elementRenderer
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory $relationDetailsCollectionFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Amasty\CustomerAttributes\Block\Widget\Form\Renderer\Fieldset $fieldsetRenderer,
        \Amasty\CustomerAttributes\Block\Widget\Form\Renderer\Element $elementRenderer,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $relationDetailsCollectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->_fieldsetRenderer = $fieldsetRenderer;
        $this->_elementRenderer = $elementRenderer;
        $this->_objectManager = $objectManager;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $context->getStoreManager();
        $this->relationCollectionFactory = $relationDetailsCollectionFactory;
        $this->session = $session;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        if ($this->getNameInLayout() == 'attribute_customer_edit') {
            $type = 'customer_account_edit';
        } else {
            $type = 'customer_attributes_registration';
        }

        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            $type
        );

        if (!$attributes || !$attributes->getSize()) {
            return;
        }
        $fieldset = $form->addFieldset(
            'group-fields-customer-attributes',
            [
                'class' => 'user-defined',
                'legend' => __('Additional Settings')
            ]
        );
        $fieldset->setRenderer($this->_fieldsetRenderer);

        $this->_customerData = [];
        if ($this->session->isLoggedIn()) {
            $this->_customerData = $this->session->getCustomer()->getData();
        }

        $this->_setFieldset($attributes, $fieldset, ['gallery']);
        $this->prepareRelations($attributes, $fieldset);

        if ($this->_customerData) {
            $form->addValues($this->_customerData);
        }

        $this->setForm($form);

    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     *
     * @return void
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_addElementTypes($fieldset);

        foreach ($attributes as $attribute) {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if (!$this->_isAttributeVisible($attribute)) {
                continue;
            }
            $attribute->setStoreId($this->_storeManager->getStore()->getId());
            if (($inputType = $attribute->getFrontend()->getInputType())
                && !in_array(
                    $attribute->getAttributeCode(),
                    $exclude
                )
                && ('media_image' != $inputType || $attribute->getAttributeCode() == 'image')
            ) {
                $typeInternal = $attribute->getTypeInternal();

                $inputTypes = [
                    'statictext' => 'note',
                    'selectgroup' => 'select'
                ];

                if ($typeInternal) {
                    $inputType = isset($inputTypes[$typeInternal])
                        ? $inputTypes[$typeInternal] : $typeInternal;
                }
                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }
                $fieldType = 'Amasty\CustomerAttributes\Block\Data\Form\Element\\' . ucfirst($fieldType);

                $data = [
                    'name' => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel(),
                    'class' => $attribute->getFrontend()->getClass(),
                    'required' => $attribute->getIsRequired() || $attribute->getRequiredOnFront(),
                    'note' => $attribute->getNote()
                ];
                if ($typeInternal == 'selectgroup'
                    && !$this->_scopeConfig->getValue('amcustomerattr/general/allow_change_group')
                    && array_key_exists($attribute->getAttributeCode(), $this->_customerData)
                ) {
                    $data['disabled'] = 'disabled';
                }

                $element = $fieldset->addField(
                    $attribute->getAttributeCode(),
                    $fieldType,
                    $data
                )->setEntityAttribute(
                    $attribute
                );

                $element->setValue($attribute->getDefaultValue());

                $element->setRenderer($this->_elementRenderer);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                /* add options / date format */
                $this->_applyTypeSpecificConfig($inputType, $element, $attribute);
            }
        }
    }

    /**
     * Check whether attribute is visible
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return bool
     */
    protected function _isAttributeVisible(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $blockName = $this->getNameInLayout();
        if ($blockName == 'attribute_customer_register') {
            $check = $attribute->getData('on_registration') == '1';
        } else {
            $check = $attribute->getData('is_visible_on_front') == '1'
                && (!($attribute->getAccountFilled() == '1'
                        && array_key_exists($attribute->getAttributeCode(), $this->_customerData)
                    )
                    || $attribute->getAccountFilled() == '0'
                );
        }

        $store = $this->storeManager->getStore()->getId();
        $stores = $attribute->getStoreIds();
        $stores = explode(',', $stores);

        return !(!$attribute || $attribute->hasIsVisible() && !$attribute->getIsVisible())
            && $check
            && in_array($store, $stores);
    }

    /**
     * {@inheritdoc}
     */
    protected function _applyTypeSpecificConfig($inputType, $element, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        switch ($inputType) {
            case 'selectimg':
                $element->addElementValues($attribute->getSource()->getAllOptions(false, false));
                break;
            case 'select':
                $element->addElementValues($attribute->getSource()->getAllOptions(true, false));
                break;
            case 'multiselectimg':
            case 'multiselect':
                $element->addElementValues($attribute->getSource()->getAllOptions(false, false));
                $element->setCanBeEmpty(true);
                break;
            case 'date':
                $element->setDateFormat($this->_localeDate->getDateFormatWithLongYear());
                break;
            case 'multiline':
                $element->setLineCount($attribute->getMultilineCount());
                break;
            default:
                break;
        }
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Form\Attribute\Collection $attributes
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    protected function prepareRelations($attributes, $fieldset)
    {
        $attributeIds = $attributes->getColumnValues('attribute_id');
        if (empty($attributeIds)) {
            return;
        }
        $dependentCollection = $this->relationCollectionFactory->create()
            ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds])
            ->joinDependAttributeCode();

        $depends = [];
        /** @var \Amasty\CustomerAttributes\Api\Data\RelationDetailInterface $relationDetail */
        foreach ($dependentCollection as $relationDetail) {
            $depends[] = [
                'parent_attribute_id' => $relationDetail->getAttributeId(),
                'parent_attribute_code' => $relationDetail->getData('parent_attribute_code'),
                'parent_option_id' => $relationDetail->getOptionId(),
                'depend_attribute_id' => $relationDetail->getDependentAttributeId(),
                'depend_attribute_code' => $relationDetail->getData('dependent_attribute_code')
            ];
        }
        if (!empty($depends)) {
            $fieldset->setData('depends', $depends);
        }
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = [
            'price' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price',
            'weight' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            'gallery' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery',
            'image' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image',
            'boolean' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean',
            'textarea' => 'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg',
        ];

        $response = $this->dataObjectFactory->create();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_element_types', ['response' => $response]);

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }
}
