<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\Relation;

use Amasty\CustomerAttributes\Controller\RegistryConstants;
use Amasty\CustomerAttributes\Model\Relation;

class DependentAttributeProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var null|array  */
    protected $options = null;

    /** @var null|int  */
    protected $excludeAttributeId = null;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\CustomerAttributes\Helper\Collection
     */
    private $collectionHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var ParentAttributeProvider
     */
    private $attributeProvider;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $collectionFactory,
        \Amasty\CustomerAttributes\Helper\Collection $collectionHelper,
        ParentAttributeProvider $attributeProvider
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionHelper = $collectionHelper;
        $this->coreRegistry = $coreRegistry;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            if (!$this->getExcludeAttributeId()) {
                return $this->options = [];
            }
            $collection = $this->collectionFactory->create()
                ->addVisibleFilter();

            $collection = $this->collectionHelper->addFilters(
                $collection,
                'eav_attribute',
                [
                    "is_user_defined = 1",
                    "attribute_code != 'customer_activated' ",
                    "attribute_id != " . $this->getExcludeAttributeId()
                ]
            );

            $this->options = [];
            foreach ($collection as $attribute) {
                $label = $attribute->getFrontendLabel();
                if (!$attribute->getIsVisibleOnFront()) {
                    $label .= ' - ' . __('Not Visible');
                }
                $this->options[] = [
                    'value' => $attribute->getAttributeId(),
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }

    /**
     * Get Parent Attribute ID
     * Dependent attribute should not be like parent attribute
     *
     * @return int|false
     */
    protected function getExcludeAttributeId()
    {
        if ($this->excludeAttributeId === null) {
            /** @var Relation $relation */
            $relation = $this->coreRegistry->registry(RegistryConstants::CURRENT_RELATION_ID);
            if ($relation instanceof Relation && $relation->getAttributeId()) {
                $this->excludeAttributeId = $relation->getAttributeId();
            } else {
                $this->excludeAttributeId = false;
                // If relation new then take first attribute from dropdown "Parent Attribute"
                $attribute = $this->attributeProvider->getDefaultSelected();
                if ($attribute) {
                    $this->excludeAttributeId = $attribute['value'];
                }
            }
        }
        return $this->excludeAttributeId;
    }

    /**
     * Force set attribute ID
     *
     * @param int $excludeAttributeId
     *
     * @return $this
     */
    public function setExcludeAttributeId($excludeAttributeId)
    {
        $this->excludeAttributeId = $excludeAttributeId;
        return $this;
    }
}
