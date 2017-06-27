<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Pgrid\Ui\Component;

class ColumnFactory extends \Magento\Catalog\Ui\Component\ColumnFactory
{
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
        'multiselect' => 'Amasty_Pgrid/js/grid/columns/multiselect',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'date' => 'date',
    ];

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

        /*
         * check name of column for exclude Role Permission Owner and
         * check Weight Type for show valid label
         */
        if ($attribute->usesSource() && $columnName !== 'amrolepermissions_owner') {
            $config['options'] = $attribute->getSource()->getAllOptions();
        }else if ($attribute->getAttributeCode() === 'weight_type'){
            $config['options'] = [
                [
                    'label' => __('This item has weight'),
                    'value' => 1
                ],
                [
                    'label' => __('This item has no weight'),
                    'value' => 0
                ],
            ];
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