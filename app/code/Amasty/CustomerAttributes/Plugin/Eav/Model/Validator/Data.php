<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Eav\Model\Validator;

class Data
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * @param \Magento\Eav\Model\Validator\Attribute\Data $subject
     * @param \Closure $proceed
     * @param $entity
     * @return \Magento\Eav\Model\Validator\Attribute\Data
     */
    public function aroundIsValid(
        \Magento\Eav\Model\Validator\Attribute\Data $subject,
        \Closure $proceed,
        $entity
    ) {
        $blacklist = [];
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'amasty_custom_attribute'
        );

        foreach ($attributes as $attribute) {
            $blacklist[] = $attribute->getAttributeCode();
        }
        $subject->setAttributesBlackList($blacklist);
        $proceed($entity);

        return $subject;
    }
}
