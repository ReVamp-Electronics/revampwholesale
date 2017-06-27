<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Grid;

class ColumnSet
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;
    /**
     * @var \Magento\Backend\Block\Widget\Grid\ColumnFactory
     */
    protected $gridColumn;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Backend\Block\Widget\Grid\ColumnFactory $gridColumn
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->gridColumn = $gridColumn;
    }


    /**
     * @param \Magento\Backend\Block\Widget\Grid\ColumnSet $subject
     * @param $result
     * @return mixed
     */
    public function afterGetColumns(\Magento\Backend\Block\Widget\Grid\ColumnSet $subject, $result)
    {
        if ( $subject->getNameInLayout() == 'adminhtml.customer.grid.columnSet'
             && $this->_scopeConfig->getValue('amcustomerattr/general/select_grid')
        ) {
            $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
                'customer',
                'amasty_custom_attribute'
            );

            foreach ($attributes as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attributeCode = $attribute->getAttributeCode();
                if ($attribute->getIsUsedInGrid() == "1"
                    && !array_key_exists($attributeCode, $result)
                ) {
                    $column = $this->gridColumn->create()
                        ->setData([
                            "id"     => $attribute->getId(),
                            "header" => $attribute->getName(),
                            "index"  => $attributeCode,
                            "type"   => 'Magento\Backend\Block\Widget\Grid\Column'
                        ])
                        ->setGrid($subject->getGrid());


                    $result[$attributeCode] = $column;
                }
            }
        }

        return $result;
    }
}
