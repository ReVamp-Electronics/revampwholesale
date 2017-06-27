<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel\CustomField;

use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class Collection
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var int|null
     */
    protected $storeId = null;

    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\CustomField', 'Aheadworks\Rma\Model\ResourceModel\CustomField');
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->walk('unserializeFields');
        if ($this->storeId !== null) {
            $this->walk('setStoreId', ['storeId' => $this->storeId]);
        }
        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    public function setFilterForRmaGrid()
    {
        $this->addFieldToFilter('refers', \Aheadworks\Rma\Model\Source\CustomField\Refers::REQUEST_VALUE)
            ->addFieldToFilter('type', ['in' => [Type::TEXT_VALUE, Type::SELECT_VALUE]])
        ;
        return $this;
    }

    /**
     * @param string $refers
     * @return $this
     */
    public function addRefersToFilter($refers)
    {
        return $this->addFieldToFilter('refers', ['eq' => $refers]);
    }

    /**
     * @param bool $isDisplay
     * @return $this
     */
    public function addDisplayInLabelFilter($isDisplay)
    {
        return $this->addFieldToFilter('is_display_in_label', ['eq' => $isDisplay]);
    }

    /**
     * @param array $attributes
     * @param int $storeId
     * @return $this
     */
    public function joinAttributesValues($attributes, $storeId)
    {
        foreach ($attributes as $attrCode) {
            $conditions = [
                "main_table.id = {$attrCode}.custom_field_id",
                $this->getConnection()->quoteInto("{$attrCode}.store_id = ?", $storeId),
                $this->getConnection()->quoteInto("attribute_code = ?", $attrCode)
            ];
            $this->getSelect()
                ->joinLeft(
                    [$attrCode => $this->getTable('aw_rma_custom_field_attr_value')],
                    implode(' AND ', $conditions),
                    [
                        $attrCode => $attrCode.'.value'
                    ]
                )
            ;
        }
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }
}