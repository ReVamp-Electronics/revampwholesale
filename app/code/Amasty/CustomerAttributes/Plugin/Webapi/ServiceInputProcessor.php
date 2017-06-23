<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Webapi;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Amasty\CustomerAttributes\Component\Form\AttributeMapper;

class ServiceInputProcessor
{
    public function beforeConvertValue($subject, $data, $type)
    {
        $attributeType = ['custom_attributes', 'customAttributes'];
        /* fix fatal error with array value from multiselect attributes*/
        foreach ($attributeType as $name) {
            if (is_array($data) && array_key_exists($name, $data)) {
                foreach ($data[$name] as $key => $attributeValue) {
                    if (is_array($attributeValue)) {
                        $data[$name][$key] = implode(',', $attributeValue);
                    }
                }
            }
        }

        return [$data, $type];
    }
}
