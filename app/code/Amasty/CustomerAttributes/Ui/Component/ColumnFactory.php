<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Ui\Component;

class ColumnFactory extends \Magento\Catalog\Ui\Component\ColumnFactory
{
    public function __construct(
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($componentFactory);
        $this->_objectManager = $objectManager;
    }

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default'       => 'text',
        'text'          => 'text',
        'boolean'       => 'select',
        'select'        => 'select',
        'multiselect'   => 'select',
        'multiselectimg'=> 'select',
        'selectimg'     => 'select',
        'selectgroup'   => 'select',
        'date'          => 'date',
    ];

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create($attribute, $context, array $config = [])
    {
        $columnName = $attribute->getAttributeCode();
        $config = array_merge([
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => $this->getDataType($attribute),
            'add_field' => true,
            'visible' => $attribute->getIsVisibleInGrid(),
            'filter' => ($attribute->getIsFilterableInGrid())
                ? $this->getFilterType($attribute->getFrontendInput())
                : null,
        ], $config);

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
        }
        if ($attribute->getFrontendInput() == 'boolean') {
            $config['options'] = $this->_objectManager->get('Amasty\CustomerAttributes\Block\Data\Form\Element\Boolean')->getValues();
        }
        
        $config['component'] = $this->getJsComponent($config['dataType']);
        
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];
        
        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

}
