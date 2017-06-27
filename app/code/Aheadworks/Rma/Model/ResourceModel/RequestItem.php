<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

/**
 * Class RequestItem
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class RequestItem extends AbstractResource
{
    /**
     * @var string
     */
    protected $customFieldTableName = 'aw_rma_request_item_custom_field_value';

    protected function _construct()
    {
        $this->_init('aw_rma_request_item', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->updateCustomFieldValues($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->attachCustomFieldValues($object);
        return parent::_afterLoad($object);
    }
}
