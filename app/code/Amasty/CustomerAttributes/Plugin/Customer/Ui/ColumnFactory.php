<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Customer\Ui;

class ColumnFactory
{
    /**
     * set magento data model for checkxoxes and radios
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function beforeCreate($subject, $attributeData, $columnName, $context, $config = []){
        if (in_array($attributeData['frontend_input'], ['selectimg', 'multiselectimg', 'selectgroup'])) {
            $attributeData['frontend_input'] = 'select';
        }

        return [$attributeData, $columnName, $context,  $config];
    }
}
