<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method \Amasty\CustomerAttributes\Api\Data\RelationDetailInterface[] getItems
 */
class Collection extends AbstractCollection
{

    public function getByRelation($relationId)
    {
        $this->addFieldToFilter('relation_id', $relationId);
        return $this;
    }

    protected function _construct()
    {
        $this->_init(
            'Amasty\CustomerAttributes\Model\RelationDetails',
            'Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails'
        );
    }

    /**
     * join EAV attribute codes
     *
     * @return $this
     */
    public function joinDependAttributeCode()
    {
        $this->getSelect()->joinInner(
            ['dependent' => $this->getTable('eav_attribute')],
            'main_table.dependent_attribute_id = dependent.attribute_id',
            ['dependent.attribute_code as dependent_attribute_code']
        )->joinInner(
            ['parent' => $this->getTable('eav_attribute')],
            'main_table.attribute_id = parent.attribute_id',
            ['parent.attribute_code as parent_attribute_code']
        );

        return $this;
    }

    /**
     * Prepare relations for attribute
     *
     * @param int $attributeId
     *
     * @return array
     */
    public function getAttributeRelations($attributeId)
    {
        return $this->addFieldToFilter('main_table.attribute_id', $attributeId)
            ->joinDependAttributeCode()
            ->toRelationArray();
    }

    /**
     * @return array
     */
    public function toRelationArray()
    {
        $relations = [];
        foreach ($this->getItems() as $relation) {
            $relations[] = [
                    'option_value'   => $relation->getOptionId(),
                    'attribute_name' => $relation->getData('parent_attribute_code'),
                    'dependent_name' => $relation->getData('dependent_attribute_code')
                ];
        }
        return $relations;
    }
}
