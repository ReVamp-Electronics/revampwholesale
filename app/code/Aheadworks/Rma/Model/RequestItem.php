<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

/**
 * Class RequestItem
 * @package Aheadworks\Rma\Model
 */
class RequestItem extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\ResourceModel\RequestItem');
    }

    /**
     * @param null $index
     * @return array|null
     */
    public function getCustomFields($index = null)
    {
        if ($this->getId() && !$this->hasData('custom_fields')) {
            $this->getResource()->attachCustomFieldValues($this);
        }
        return $this->getData('custom_fields', $index);
    }

    /**
     * @param int|string $id
     * @param string $default
     * @return string
     */
    public function getCustomFieldValue($id, $default = '')
    {
        if (is_numeric($id)) {
            return $this->getCustomFields($id);
        }
        $value = $this->getResource()->getCustomFieldValueByName($this, $id);
        return $value ? : $default;
    }
}