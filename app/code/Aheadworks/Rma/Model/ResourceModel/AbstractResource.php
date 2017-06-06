<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Model\Source\CustomField\Type as CustomFieldType;

abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $attributeCodes = [];

    protected $entityRefFieldName = 'entity_id';

    /**
     * @var null|string
     */
    protected $attrTableName = null;

    /**
     * @var null|string
     */
    protected $customFieldTableName = null;

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function updateAttributeValues(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->attrTableName === null) {
            return $this;
        }

        $connection = $this->getConnection();
        $table = $this->getTable($this->attrTableName);
        foreach ($this->attributeCodes as $attributeCode) {
            $attrValues = $object->getAttribute($attributeCode);
            if (!is_array($attrValues)) {
                continue;
            }

            $originalAttrValues = $object->getOrigData('attribute');
            if (!$originalAttrValues || !is_array($originalAttrValues)) {
                $originalAttrValues = [];
            } else {
                $originalAttrValues = isset($originalAttrValues[$attributeCode]) ? $originalAttrValues[$attributeCode] : [];
            }

            $toDelete = array_diff_key($originalAttrValues, $attrValues);
            $toInsert = array_diff_key($attrValues, $originalAttrValues);
            $toUpdate = array_intersect_key($attrValues, $originalAttrValues);
            foreach ($toInsert as $storeId => $value) {
                $connection->insert(
                    $table,
                    [
                        'store_id' => $storeId,
                        'attribute_code' => $attributeCode,
                        'value' => $value,
                        $this->entityRefFieldName => $object->getId()
                    ]
                );
            }
            foreach ($toUpdate as $storeId => $value) {
                if ($originalAttrValues[$storeId] != $value) {
                    $connection->update(
                        $table,
                        ['value' => $value],
                        [
                            "attribute_code = ?" => $attributeCode,
                            "{$this->entityRefFieldName} = ?" => $object->getId(),
                            "store_id = ?" => $storeId
                        ]
                    );
                }
            }
            foreach ($toDelete as $storeId => $value) {
                $connection->delete(
                    $table,
                    [
                        "attribute_code = ?" => $attributeCode,
                        "{$this->entityRefFieldName} = ?" => $object->getId(),
                        "store_id = ?" => $storeId
                    ]
                );
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function attachAttributeValues(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->attrTableName === null) {
            return $this;
        }

        $attributeData = [];
        $connection = $this->getConnection();
        foreach ($this->attributeCodes as $attributeCode) {
            $columns = [
                'store_id' => 'store_id',
                'value' => 'value'
            ];
            $select = $connection->select()
                ->from($this->getTable($this->attrTableName), $columns)
                ->where("attribute_code = ?", $attributeCode)
                ->where("{$this->entityRefFieldName} = ?", $object->getId())
            ;
            if ($object->getStoreId()) {
                $select->where("store_id = ?", $object->getStoreId());
            }
            foreach ($connection->fetchAll($select) as $data) {
                if ($object->getStoreId()) {
                    $attributeData[$attributeCode] = $data['value'];
                } else {
                    $attributeData[$attributeCode][$data['store_id']] = $data['value'];
                }
            }
        }
        $object->setAttribute($attributeData);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function deleteAttributeValues(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->attrTableName === null) {
            return $this;
        }

        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable($this->attrTableName),
            $connection->quoteInto("{$this->entityRefFieldName} = ?", $object->getId())
        );

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function updateCustomFieldValues(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->customFieldTableName === null) {
            return $this;
        }

        $customFields = $object->getCustomFields();
        if (!is_array($customFields)) {
            return $this;
        }
        $origCustomFields = $object->getOrigData('custom_fields');
        if (!is_array($origCustomFields)) {
            $origCustomFields = [];
        }

        $connection = $this->getConnection();
        $table = $this->getTable($this->customFieldTableName);

        $toDelete = array_diff_key($origCustomFields, $customFields);
        $toInsert = array_diff_key($customFields, $origCustomFields);
        $toUpdate = array_intersect_key($customFields, $origCustomFields);
        foreach ($toInsert as $fieldId => $value) {
            if (is_array($value)) {
                $value = implode(",",$value);
            }
            $connection->insert(
                $table,
                [
                    'entity_id' => $object->getId(),
                    'field_id' => $fieldId,
                    'value' => $value,
                ]
            );
        }
        foreach ($toUpdate as $fieldId => $value) {
            if (is_array($value)) {
                $value = implode(",",$value);
            }
            if ($origCustomFields[$fieldId] != $value) {
                $connection->update(
                    $table,
                    ['value' => $value],
                    [
                        "entity_id = ?" => $object->getId(),
                        "field_id = ?" => $fieldId,
                    ]
                );
            }
        }
        foreach ($toDelete as $fieldId => $value) {
            $connection->delete(
                $table,
                [
                    "entity_id = ?" => $object->getId(),
                    "field_id = ?" => $fieldId,
                ]
            );
        }
        $object->setOrigData('custom_fields', $customFields);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function attachCustomFieldValues(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->customFieldTableName === null) {
            return $this;
        }

        $customFieldsData = [];
        $connection = $this->getConnection();
        $columns = [
            'field_id' => 'field_id',
            'value' => 'value'
        ];
        $select = $connection->select()
            ->from(['cf_value' => $this->getTable($this->customFieldTableName)], $columns)
            ->join(['cf' => $this->getTable('aw_rma_custom_field')], 'cf.id = cf_value.field_id', ['type' => 'cf.type'])
            ->where("entity_id = ?", $object->getId())
        ;
        foreach ($connection->fetchAll($select) as $data) {
            if ($data['type'] == CustomFieldType::MULTI_SELECT_VALUE) {
                $data['value'] = $data['value'] ? explode(",", $data['value']) : "";
            }
            $customFieldsData[$data['field_id']] = $data['value'];
        }
        $object->setCustomFields($customFieldsData);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param $name
     * @return null
     */
    public function getCustomFieldValueByName(\Magento\Framework\Model\AbstractModel $object, $name)
    {
        if ($this->customFieldTableName === null) {
            return null;
        }

        $connection = $this->getConnection();
        $mainTable = $this->getTable($this->customFieldTableName);
        $select = $connection->select()
            ->from($mainTable, ['value' => 'value'])
            ->joinLeft(
                ['custom_field_table' => $this->getTable('aw_rma_custom_field')],
                "{$mainTable}.field_id = custom_field_table.id",
                ['name' => 'custom_field_table.name']
            )
            ->where("custom_field_table.name = ?", $name)
        ;

        if ($result = $connection->fetchRow($select)) {
            return $result['value'];
        }
        return null;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $validators
     * @return $this
     * @throws LocalizedException
     */
    protected function validateAttributes(\Magento\Framework\Model\AbstractModel $object, $validators = [])
    {
        $messages = [];
        foreach ($validators as $attrCode => $validators) {
            if ($object->getAttribute($attrCode)) {
                foreach ($object->getAttribute($attrCode) as $attrValue) {
                    foreach ($validators as $validator) {
                        /** @var \Zend_Validate_Interface $validator */
                        if (!$validator->isValid($attrValue)) {
                            $messages = array_merge($messages, array_values($validator->getMessages()));
                        }
                    }
                }
            }
        }
        if (!empty($messages)) {
            throw new LocalizedException(__(array_shift($messages)));
        }
        return $this;
    }
}
