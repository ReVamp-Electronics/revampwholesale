<?php

namespace IWD\SalesRep\Model\Preference\Customer\Data;

use IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer\Collection as CustomerCollectionPlugin;
use IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class Customer
 * @package IWD\SalesRep\Model\Preference\Customer\Data
 */
class Customer extends \Magento\Customer\Model\Data\Customer
{
    /**
     * @param $id
     * @return $this
     */
    public function setAssignedSalesrepId($id)
    {
        return $this->setData(CustomerCollectionPlugin::KEY_ASSIGNED_SALESREP_ID, $id);
    }

    /**
     * @param $type
     * @return $this
     */
    public function setCommissionType($type)
    {
        return $this->setData(AttachedCustomer::COMMISSION_TYPE, $type);
    }

    /**
     * @param $applyWhen
     * @return $this
     */
    public function setCommissionApply($applyWhen)
    {
        return $this->setData(AttachedCustomer::COMMISSION_APPLY_WHEN, $applyWhen);
    }

    /**
     * @param $rate
     * @return $this
     */
    public function setCommissionRate($rate)
    {
        return $this->setData(AttachedCustomer::COMMISSION_RATE, $rate);
    }

    /**
     * @return int|null
     */
    public function getAssignedSalesrepId()
    {
        return $this->_get(CustomerCollectionPlugin::KEY_ASSIGNED_SALESREP_ID);
    }

    /**
     * @return string|null
     */
    public function getCommissionType()
    {
        return $this->_get(AttachedCustomer::COMMISSION_TYPE);
    }

    /**
     * @return string|null
     */
    public function getCommissionApply()
    {
        return $this->_get(AttachedCustomer::COMMISSION_APPLY_WHEN);
    }

    /**
     * @return string|null
     */
    public function getCommissionRate()
    {
        return $this->_get(AttachedCustomer::COMMISSION_RATE);
    }
}
