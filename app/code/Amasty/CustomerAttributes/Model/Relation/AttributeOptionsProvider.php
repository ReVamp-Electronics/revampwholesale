<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\Relation;

use Amasty\CustomerAttributes\Controller\RegistryConstants;
use Amasty\CustomerAttributes\Model\Relation;

class AttributeOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var null|array  */
    protected $options = null;

    /** @var null|int  */
    protected $parentAttributeId = null;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    private $optionCollection;

    /**
     * @var ParentAttributeProvider
     */
    private $attributeProvider;

    /**
     * AttributeOptionsProvider constructor.
     *
     * @param \Magento\Framework\Registry                                                $coreRegistry
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $collectionFactory
     * @param ParentAttributeProvider                                                    $attributeProvider
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $collectionFactory,
        ParentAttributeProvider $attributeProvider
    ) {
        $this->optionCollection = $collectionFactory->create();
        $this->coreRegistry     = $coreRegistry;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            if (!$this->getSelectedParentAttribute()) {
                return $this->options = [];
            }
            $this->options = $this->optionCollection
                ->setAttributeFilter($this->getSelectedParentAttribute())
                /* join default option labels */
                ->setStoreFilter(0, false)
                ->toOptionArray();
        }

        return $this->options;
    }

    /**
     * Get selected Attribute ID for load Options
     *
     * @return int|false
     */
    public function getSelectedParentAttribute()
    {
        if ($this->parentAttributeId === null) {
            /** @var Relation $relation */
            $relation = $this->coreRegistry->registry(RegistryConstants::CURRENT_RELATION_ID);
            if ($relation instanceof Relation && $relation->getAttributeId()) {
                $this->parentAttributeId = $relation->getAttributeId();
            } else {
                $this->parentAttributeId = false;
                // If relation new then take first attribute from dropdown "Parent Attribute"
                $attribute = $this->attributeProvider->getDefaultSelected();
                if ($attribute) {
                    $this->parentAttributeId = $attribute['value'];
                }
            }
        }

        return $this->parentAttributeId;
    }

    /**
     * Force set attribute ID
     *
     * @param $attributeId
     *
     * @return $this
     */
    public function setParentAttributeId($attributeId)
    {
        $this->parentAttributeId = $attributeId;
        return $this;
    }
}
