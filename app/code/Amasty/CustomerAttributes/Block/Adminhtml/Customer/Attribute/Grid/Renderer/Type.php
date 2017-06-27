<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Input;
use Magento\Framework\DataObject;

class Type extends Input
{
    public function render(DataObject $row)
    {
        $names = [
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
        $type = $row->getFrontendInput();
        $internal = $row->getTypeInternal();
        if ($internal == 'selectgroup') {
            $type = $internal;
        }
        $html = isset($names[$type]) ? $names[$type] : '';
        return $html;
    }
}
