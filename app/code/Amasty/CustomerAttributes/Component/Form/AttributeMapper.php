<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Component\Form;

use Amasty\CustomerAttributes\Model\CustomerFormManager;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class AttributeMapper extends \Magento\Ui\Component\Form\AttributeMapper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Amasty\CustomerAttributes\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\CollectionFactory
     */
    private $relationCollectionFactory;

    /**
     * @var \Amasty\CustomerAttributes\Block\Data\Form\Element\Boolean
     */
    private $booleanElement;

    /**
     * AttributeMapper constructor.
     * @param TimezoneInterface $localeData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Amasty\CustomerAttributes\Helper\Image $imageHelper
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        TimezoneInterface $localeData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\CustomerAttributes\Helper\Image $imageHelper,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\CollectionFactory $relationCollectionFactory,
        \Amasty\CustomerAttributes\Block\Data\Form\Element\Boolean $booleanElement
    ) {
        $this->localeDate            = $localeData;
        $this->_objectManager        = $objectManager;
        $this->imageHelper           = $imageHelper;
        $this->groupRepository       = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->relationCollectionFactory = $relationCollectionFactory;
        $this->booleanElement = $booleanElement;
    }
    
    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElementMap = [
        'text'    => 'input',
        'hidden'  => 'input',
        'boolean' => 'select',
    ];

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    protected $metaPropertiesMap = [
        'dataType'       => 'getFrontendInput',
        'visible'        => 'getIsVisible',
        'required'       => 'getIsRequired',
        'label'          => 'getStoreLabel',
        'sortOrder'      => 'getSortOrder',
        'notice'         => 'getNote',
        'default'        => 'getDefaultValue',
        'frontend_class' => 'getFrontendClass',
        'size'           => 'getMultilineCount'
    ];

    /**
     * @var array
     */
    protected $validationRules = [
        'input_validation' => [
            'email' => ['validate-email' => true],
            'date'  => ['validate-date' => true],
        ],
    ];

    protected $dataTypes = [
        'textarea' => 'text'
    ];

    /**
     * Get attributes meta
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function map($attribute)
    {
        foreach ($this->metaPropertiesMap as $metaName => $methodName) {
            $value = $attribute->$methodName();
            $meta[$metaName] = $value;

            switch ($methodName) {
                case 'getFrontendInput':
                    $meta['formElement'] = isset($this->formElementMap[$value])
                        ? $this->formElementMap[$value]
                        : $value;
                    break;
                case 'getStoreLabel':
                    $meta[$metaName] = __($meta[$metaName]);
                    break;
                case 'getSortOrder':
                    $meta['sortOrder'] += CustomerFormManager::ORDER_OFFSET;//fix to move attributes to the bottom
                    break;
            }
        }
        if ($attribute->getRequiredOnFront()) {
            $meta['required'] = 1;
        }

        $meta['options'] = $this->prepareOptions($attribute);

        $meta['validation'] = $this->getValidationRules($meta);

        if ($elementTmpl = $this->getElementTmpl($attribute->getFrontendInput())) {
            $meta['config']['elementTmpl'] = $elementTmpl;
        }

        $meta['config']['relations'] = $this->getElementRelations($attribute);

        return $meta;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     *
     * @return array|null
     */
    protected function prepareOptions($attribute)
    {
        switch ($attribute->getFrontendInput()) {
            case 'datetime':
                return [
                    'showsTime'  => true,
                    'timeFormat' => $this->localeDate->getTimeFormat(),
                ];
            case 'boolean':
                return $this->booleanElement->getValues();
        }
        if (!$attribute->usesSource()) {
            return null;
        }
        $displayEmptyOption = $this->displayEmptyOption($attribute);
        $allOptions = $attribute->getSource()->getAllOptions($displayEmptyOption);
        foreach ($allOptions as $key => $option) {
            if ($option['label'] == "") {
                $option['label'] = " ";
                continue;
            }
            $allOptions[$key]['icon'] = $this->imageHelper->getIconUrl($option['value']);
        }

        return array_values($allOptions);
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     *
     * @return bool
     */
    protected function displayEmptyOption($attribute)
    {
        switch ($attribute->getFrontendInput()) {
            case 'radios':
            case 'checkboxes':
            case 'multiselect':
            case 'multiselectimg':
            case 'selectimg':
                $displayEmptyOption = false;
                break;
            default:
                $displayEmptyOption = true;
                break;
        }

        return $displayEmptyOption;
    }

    /**
     * Get frontend validation rules
     *
     * @param array $meta
     *
     * @return array
     */
    protected function getValidationRules($meta)
    {
        $rules = [];
        if (isset($meta['required']) && $meta['required'] == 1) {
            $rules['required-entry'] = true;
        }
        if (isset($meta['frontend_class'])) {
            if ($meta['frontend_class'] == 'validate-length') {
                $maxLength = 25;
                $rules[$meta['frontend_class']] = 'maximum-length-' . $maxLength;
                $rules['max_text_length'] = $maxLength;
            } else {
                $rules[$meta['frontend_class']] = true;
            }
        }

        return $rules;
    }

    /**
     * @param string $attributeFrontendInput
     *
     * @return string
     */
    protected function getElementTmpl($attributeFrontendInput)
    {
        switch ($attributeFrontendInput) {
            case 'radios':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/radios';
                break;
            case 'checkboxes':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/checkboxes';
                break;
            case 'datetime':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/datetime';
                break;
            case 'multiselect':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/multiselect';
                break;
            case 'multiselectimg':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/multiselectimg';
                break;
            case 'selectimg':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/selectimg';
                break;
            case 'statictext':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/statictext';
                break;
            default:
                $elementTmpl = '';
                break;
        }

        return $elementTmpl;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     *
     * @return array|false
     */
    protected function getElementRelations($attribute)
    {
        $relations = false;
        switch ($attribute->getFrontendInput()) {
            case 'selectimg':
            case 'multiselectimg':
            case 'multiselect':
            case 'select':
                $relations = $this->relationCollectionFactory->create()
                    ->getAttributeRelations($attribute->getAttributeId());
                break;
        }

        return $relations;
    }
}
