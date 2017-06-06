<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Model\Source\CustomField\Type;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Magento\Store\Model\Store;

/**
 * Class CustomField
 * @package Aheadworks\Rma\Model
 */
class CustomField extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var int|null
     */
    protected $storeId = null;

    /**
     * @var null|array
     */
    protected $optionArray = null;

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        if (!$this->hasType()) {
            $this->setType(Type::SELECT_VALUE);
        }
        if (!$this->hasRefers()) {
            $this->setRefers(Refers::REQUEST_VALUE);
        }
        if (!$this->hasIsRequired()) {
            $this->setIsRequired(true);
        }
        $this->_init('Aheadworks\Rma\Model\ResourceModel\CustomField');
    }

    /**
     * @param string $name
     * @return $this
     */
    public function loadByName($name)
    {
        return $this->load($name, 'name');
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

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @return $this
     */
    public function unserializeFields()
    {
        $this->getResource()->unserializeFields($this);
        return $this;
    }

    /**
     * @param null $index
     * @return string
     */
    public function getOption($index = null)
    {
        if ($this->getId() && !$this->hasData('option')) {
            $this->getResource()->attachOptions($this);
        }
        return $this->getData('option', $index);
    }

    /**
     * Retrieves all options, if custom field is system; only enabled options - otherwise
     *
     * @return array|null
     */
    public function toOptionArray()
    {
        if ($this->optionArray === null) {
            $storeId = $this->getStoreId() !== null ? $this->getStoreId() : Store::DEFAULT_STORE_ID;
            $optionValues = $this->getOption('value');
            $enable = $this->getOption('enable');
            if ($optionValues) {
                $this->optionArray = [];
                foreach ($optionValues as $valueId => $values) {
                    if (in_array($valueId, $enable)) {
                        $this->optionArray[] = [
                            'value' => $valueId,
                            'label' => isset($values[$storeId]) ? $values[$storeId] : $values[Store::DEFAULT_STORE_ID]
                        ];
                    }
                }
            }
        }
        return $this->optionArray;
    }

    /**
     * Retrieves option label by value. Takes into account all options
     *
     * @param $value
     * @return string|null
     */
    public function getOptionLabelByValue($value)
    {
        $storeId = $this->getStoreId() !== null ? $this->getStoreId() : Store::DEFAULT_STORE_ID;
        $optionValues = $this->getOption('value');
        if (isset($optionValues[$value])) {
            return  isset($optionValues[$value][$storeId]) ?
                $optionValues[$value][$storeId] :
                $optionValues[$value][Store::DEFAULT_STORE_ID];
        }
        return null;
    }

    /**
     * Compares option value with default one
     *
     * @param int $value
     * @return bool
     */
    public function getIsDefault($value)
    {
        $result = false;
        $default = $this->getOption('default');
        if (is_array($default)) {
            $result = in_array($value, $default);
        }
        return $result;
    }
}