<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Helper;

use Magento\Eav\Helper\Data;

class Config
{
    /**
     * @var \Magento\Eav\Helper\Data $_eavData
     */
    protected $_eavData;

    public function __construct(
        Data $eavData
    )
    {
        $this->_eavData = $eavData;
    }

    public function getAttributeTypes($asHash = false)
    {
        if ($asHash) {
            return [
                'text' => __('Text Field'),
                'textarea' => __('Text Area'),
                'date' => __('Date'),
                'multiselect' => __('Multiple Select'),
                'multiselectimg' => __('Multiple Checkbox Select with Images'),
                'select' => __('Dropdown'),
                'boolean' => __('Yes/No'),
                'selectimg' => __('Single Radio Select with Images'),
                'selectgroup' => __('Customer Group Selector'),
                'statictext' => __('Static Text'),
                'file' => __('Single File Upload')
            ];
        }
        return [
            [
                'value' => 'text',
                'label' => __('Text Field')
            ],
            [
                'value' => 'textarea',
                'label' => __('Text Area')
            ],
            [
                'value' => 'date',
                'label' => __('Date')
            ],
            [
                'value' => 'multiselect',
                'label' => __('Multiple Select')
            ],
            [
                'value' => 'multiselectimg',
                'label' => __('Multiple Checkbox Select with Images')
            ],
            [
                'value' => 'select',
                'label' => __('Dropdown')
            ],
            [
                'value' => 'boolean',
                'label' => __('Yes/No')
            ],
            [
                'value' => 'selectimg',
                'label' => __('Single Radio Select with Images')
            ],
            [
                'value' => 'selectgroup',
                'label' => __('Customer Group Selector')
            ],
            [
                'value' => 'statictext',
                'label' => __('Static Text')
            ],
            [
                'value' => 'file',
                'label' => __('Single File Upload')
            ],
        ];
    }

    public function getValidationRules()
    {
        $result = $this->_eavData->getFrontendClasses(null);
        return $result;
    }

}