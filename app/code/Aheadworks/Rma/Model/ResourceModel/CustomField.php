<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class CustomField
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class CustomField extends AbstractResource
{
    /**
     * @var array
     */
    protected $attributeCodes = ['frontend_label'];

    /**
     * @var string
     */
    protected $entityRefFieldName = 'custom_field_id';

    /**
     * @var string
     */
    protected $attrTableName = 'aw_rma_custom_field_attr_value';

    /**
     * @var array
     */
    protected $_serializableFields = [
        'website_ids' => [[], []],
        'visible_for_status_ids' => [[], []],
        'editable_for_status_ids' => [[], []],
        'editable_admin_for_status_ids' => [[], []]
    ];

    protected function _construct()
    {
        $this->_init('aw_rma_custom_field', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (in_array($object->getType(), [Type::TEXT_VALUE, Type::TEXT_AREA_VALUE])) {
            $object->unsOption();
        }

        $this->validateAttributes($object);
        $this->validateOptions($object);
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->updateAttributeValues($object);
        $this->updateOptions($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->attachAttributeValues($object);
        $this->attachOptions($object);
        return parent::_afterLoad($object);
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $object
     * @return $this
     */
    protected function updateOptions(\Aheadworks\Rma\Model\CustomField $object)
    {
        $option = $object->getOption();
        if (!is_array($option)) {
            return $this;
        }

        $defaultValue = (isset($option['default']) && is_array($option['default'])) ? $option['default'] : [];
        $enableValue = (isset($option['enable']) && is_array($option['enable'])) ? $option['enable'] : [];
        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = $this->updateOptionRow($object, $optionId, $defaultValue, $enableValue, $option);
                if ($intOptionId === false) {
                    continue;
                }
                $this->updateOptionValueRows($intOptionId, $values);
            }
        }
        return $this;
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $object
     * @param int $optionId
     * @param array $defaultValue
     * @param array $enableValue
     * @param array $option
     * @return bool|int
     */
    protected function updateOptionRow(\Aheadworks\Rma\Model\CustomField $object, $optionId, $defaultValue, $enableValue, $option)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('aw_rma_custom_field_option');
        $intOptionId = (int)$optionId;

        if (!empty($option['delete'][$optionId])) {
            if ($intOptionId) {
                $connection->delete($table, ['id = ?' => $intOptionId]);
            }
            return false;
        }

        $bind = [
            'sort_order' => empty($option['order'][$optionId]) ? 0 : $option['order'][$optionId],
            'is_default' => in_array($optionId, $defaultValue),
            'enabled' => in_array($optionId, $enableValue)
        ];
        if (!$intOptionId) {
            $connection->insert($table, array_merge($bind, ['field_id' => $object->getId()]));
            $intOptionId = $connection->lastInsertId($table);
        } else {
            $connection->update($table, $bind, ['id = ?' => $intOptionId]);
        }
        return $intOptionId;
    }

    /**
     * @param $optionId
     * @param $values
     */
    protected function updateOptionValueRows($optionId, $values)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('aw_rma_custom_field_option_value');

        $connection->delete($table, ['option_id = ?' => $optionId]);

        foreach ($values as $storeId => $value) {
            if ($value) {
                $connection->insert(
                    $table,
                    [
                        'option_id' => $optionId,
                        'store_id' => $storeId,
                        'value' => $value
                    ]
                );
            }
        }
    }

    /**
     * Attach option values to object
     *
     * @param \Aheadworks\Rma\Model\CustomField $object
     * @return $this
     */
    public function attachOptions(\Aheadworks\Rma\Model\CustomField $object)
    {
        $object->setOption($this->prepareOptionData($object, $this->getOptionRows($object)));
        return $this;
    }

    /**
     * Retrieves option rows data
     *
     * @param \Aheadworks\Rma\Model\CustomField $object
     * @return array
     */
    protected function getOptionRows(\Aheadworks\Rma\Model\CustomField $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('aw_rma_custom_field_option'))
            ->where("field_id = ?", $object->getId())
            ->order("sort_order ASC")
        ;
        return $connection->fetchAll($select);
    }

    /**
     * Retrieves option value rows data
     *
     * @param \Aheadworks\Rma\Model\CustomField $object
     * @param int $optionId
     * @return array
     */
    protected function getOptionValueRows(\Aheadworks\Rma\Model\CustomField $object, $optionId)
    {
        $defaultStoreId =  \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTable('aw_rma_custom_field_option_value'),
                ['store_id', 'value']
            )
            ->where("option_id = ?", $optionId)
        ;
        if ($object->getStoreId() !== null) {
            $select->where("store_id = ? OR store_id = {$defaultStoreId}", $object->getStoreId());
        }
        return $connection->fetchAll($select);
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $object
     * @param array $optionRows
     * @return array
     */
    protected function prepareOptionData(\Aheadworks\Rma\Model\CustomField $object, $optionRows)
    {
        $optionData = ['order' => [], 'value' => [], 'default' => [], 'enable' => []];
        foreach ($optionRows as $row) {
            $optionId = $row['id'];
            $optionData['order'][$optionId] = $row['sort_order'];

            $optionValueData = [];
            foreach ($this->getOptionValueRows($object, $optionId) as $valueRow) {
                $optionValueData[$valueRow['store_id']] = $valueRow['value'];
            }
            $optionData['value'][$optionId] = $optionValueData;
            if ((int)$row['is_default'] && !in_array($optionId, $optionData['default'])) {
                $optionData['default'][] = $optionId;
            }
            if ((int)$row['enabled'] && !in_array($optionId, $optionData['enable'])) {
                $optionData['enable'][] = $optionId;
            }
        }
        return $optionData;
    }

    /**
     * @return \Magento\Framework\Validator\DataObject|null
     */
    public function getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\DataObject();

        $nameNotEmpty = new \Zend_Validate_NotEmpty();
        $nameNotEmpty->setMessage(__('Name is required.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $validator->addRule($nameNotEmpty, 'name');

        return $validator;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $validators
     * @return $this
     */
    protected function validateAttributes(\Magento\Framework\Model\AbstractModel $object, $validators = [])
    {
        $frontendLabelNotEmpty = new \Zend_Validate_NotEmpty();
        $frontendLabelNotEmpty->setMessage('Frontend Label is required.', \Zend_Validate_NotEmpty::IS_EMPTY);
        $validators = [
            'frontend_label' => [$frontendLabelNotEmpty],
        ];
        return parent::validateAttributes($object, $validators);
    }

    protected function validateOptions(\Magento\Framework\Model\AbstractModel $object)
    {
        $option = $object->getOption();
        if (!is_array($option)) {
            return $this;
        }
        foreach ($option['value'] as $valueKey => $valueData) {
            foreach ($valueData as $storeId => $value) {
                if ($storeId == Store::DEFAULT_STORE_ID) {
                    if (empty($value) && !$option['delete'][$valueKey]) {
                        throw new LocalizedException(__("Option value is required."));
                    }
                }
            }
        }
        return $this;
    }
}
