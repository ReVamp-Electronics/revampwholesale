<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Plugin\Quote\Address;

class CustomAttributeList
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $_attributeMetadataDataProvider;
    protected $_attributes;

    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Amasty\CustomerAttributes\Component\Form\AttributeMapper $attributeMapper
    ) {
        $this->_attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->_attributes = [];
    }

    public function afterGetAttributes(
        $subject,
        $result
    ) {
        if (!$this->_attributes) {
            /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
            $attributes = $this->_attributeMetadataDataProvider->loadAttributesCollection(
                'customer',
                'customer_attributes_checkout'
            );
            $elements = [];
            foreach ($attributes as $attribute) {
                $key = $attribute->getAttributeCode();
                $elements[$key] = $this->attributeMapper->map($attribute);
            }

            $this->_attributes = array_merge($result, $elements);
        }
        return  $this->_attributes;
    }
}
