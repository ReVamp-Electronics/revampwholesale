<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Plugin\Customer\Metadata;

class Form
{
    /**
     * set magento data model for checkxoxes and radios
     *
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetAllowedAttributes($subject, $result)
    {
        foreach ($result as $attributeCode => $attribute) {
            if ($attribute->getFrontendInput() == 'multiselectimg'
                || $attribute->getFrontendInput() == 'selectimg'
            ) {
                $attribute->setDataModel('Magento\Customer\Model\Metadata\Form\Multiselect');
            }
            if ($attribute->getFrontendInput() == 'statictext') {
                $attribute->setDataModel('Magento\Customer\Model\Metadata\Form\Text');
            }
        }

        return $result;
    }
}
