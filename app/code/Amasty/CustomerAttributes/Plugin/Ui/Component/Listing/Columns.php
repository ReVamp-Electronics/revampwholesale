<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Plugin\Ui\Component\Listing;

/**
 * Class Columns
 */
class Columns
{
    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var array
     */
    protected $filterMap
        = [
            'default'       => 'text',
            'select'        => 'select',
            'boolean'       => 'select',
            'multiselect'   => 'select',
            'multiselectimg'=> 'select',
            'selectimg'     => 'select',
            'selectgroup'   => 'select',
            'date'          => 'dateRange',
            'datetime'      => 'dateRange',
        ];

    /**
     * @var \Amasty\CustomerAttributes\Ui\Component\ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * Columns constructor.
     *
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Amasty\CustomerAttributes\Ui\Component\ColumnFactory $columnFactory
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Amasty\CustomerAttributes\Ui\Component\ColumnFactory $columnFactory
    ) {
        $this->columnFactory = $columnFactory;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $subject
     * @param \Closure                              $proceed
     */
    public function aroundPrepare(\Magento\Ui\Component\Listing\Columns $subject, \Closure $proceed)
    {
        if ($this->allowToAddAttributes($subject)) {
            $this->prepareAttributes($subject);
        }

        $proceed();
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $columnsComponent
     */
    protected function prepareAttributes($columnsComponent)
    {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $components = $columnsComponent->getChildComponents();
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'amasty_custom_attribute'
        );
        foreach ($attributes as $attribute) {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getUsedInOrderGrid() && !isset($components[$attributeCode])) {
                $config = [
                    'sortOrder' => ++$columnSortOrder,
                    'add_field' => false,
                    'visible' => true,
                    'filter' => false,
                    //'filter' => $this->getCustomerAttributeFilterType($attribute->getFrontendInput()),
                ];
                $column = $this->columnFactory->create($attribute, $columnsComponent->getContext(), $config);
                $column->prepare();
                $columnsComponent->addComponent($attributeCode, $column);
            }
        }
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getCustomerAttributeFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }

    /**
     * Is can add Customer Attribute Columns to Component
     *
     * @param \Magento\Ui\Component\Listing\Columns $columnsComponent
     *
     * @return bool
     */
    public function allowToAddAttributes($columnsComponent)
    {
        return $columnsComponent->getName() == 'sales_order_columns';
    }
}
