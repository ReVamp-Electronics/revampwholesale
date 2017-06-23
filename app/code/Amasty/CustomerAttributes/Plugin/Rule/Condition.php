<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Plugin\Rule;

/**
 * Plugin name = Amasty_CustomerAttributes::add-condition-types
 */
class Condition
{
    /**
     * Add condition operators for new input type
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $resuorce
     * @param array                                           $operatorInputByType
     *
     * @return array
     */
    public function afterGetDefaultOperatorInputByType(
        \Magento\Rule\Model\Condition\AbstractCondition $resuorce,
        $operatorInputByType
    ) {
        $operatorInputByType['selectimg'] = $operatorInputByType['select'];
        $operatorInputByType['multiselectimg'] = $operatorInputByType['multiselect'];

        return $operatorInputByType;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $resuorce
     * @param string                                          $valueType
     *
     * @return string
     */
    public function afterGetValueElementType(
        \Magento\Rule\Model\Condition\AbstractCondition $resuorce,
        $valueType
    ) {
        if ($valueType == 'multiselectimg') {
            return 'multiselect';
        }
        if ($valueType == 'selectimg') {
            return 'select';
        }

        return $valueType;
    }
}
