<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Grid;

class Grid
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }
    /**
     * @param $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection
     * @return mixed
     */
    public function aroundGetCollection($subject, \Closure $proceed)
    {
        $collection = $proceed();
        if ('adminhtml.customer.grid.container' == $subject->getNameInLayout()
            && $this->_scopeConfig->getValue('amcustomerattr/general/select_grid')
        ) {
            $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
                'customer',
                'amasty_custom_attribute'
            );

            foreach ($attributes as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attributeCode = $attribute->getAttributeCode();
                if ($attribute->getIsUsedInGrid() == "1") {
                    $collection->addAttributeToSelect(
                        $attributeCode
                    );
                }
            }
        }

        return $collection;
    }
}
