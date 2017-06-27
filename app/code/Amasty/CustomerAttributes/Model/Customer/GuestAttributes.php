<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\Customer;

/**
 * @method \Amasty\CustomerAttributes\Model\ResourceModel\Customer\GuestAttributes getResource()
 * @method \Amasty\CustomerAttributes\Model\ResourceModel\Customer\GuestAttributes _getResource()
 */
class GuestAttributes extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('Amasty\CustomerAttributes\Model\ResourceModel\Customer\GuestAttributes');
    }

    public function getFields()
    {
        $fields = $this->getResource()->getFields();

        return $fields;
    }

    public function deleteFields($namesDel)
    {
        $this->getResource()->deleteFields($namesDel);
    }

    public function addFields($namesAdd, $attributeType)
    {
        $this->getResource()->addFields($namesAdd, $attributeType);
    }

    public function loadByOrderId($orderId)
    {
        $this->_getResource()->loadByOrderId($this, $orderId);

        return $this;
    }
}
