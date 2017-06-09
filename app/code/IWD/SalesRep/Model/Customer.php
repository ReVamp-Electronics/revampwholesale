<?php

namespace IWD\SalesRep\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    const ENTITY_ID = 'entity_id';
    const SALESREP_ID = 'salesrep_id';
    const CUSTOMER_ID = 'customer_id';
    const COMMISSION_TYPE = 'commission_type';
    const COMMISSION_RATE = 'commission_rate';
    const COMMISSION_APPLY_WHEN = 'commission_apply';

    const COMMISSION_TYPE_FIXED = 'fixed';
    const COMMISSION_TYPE_PERCENT = 'percent';

    const COMMISSION_APPLY_BEFORE = 'before';
    const COMMISSION_APPLY_AFTER = 'after';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'salesrep_attached_customer';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\ResourceModel\Customer');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return mixed
     */
    public function getSalesrepId()
    {
        return $this->getData(self::SALESREP_ID);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setSalesrepId($id)
    {
        return $this->setData(self::SALESREP_ID, $id);
    }

    /**
     * @return array
     */
    public function getCommissionTypeOptions()
    {
        return [
            self::COMMISSION_TYPE_FIXED => 'Fixed',
            self::COMMISSION_TYPE_PERCENT => '% Percent'
        ];
    }

    /**
     * @return array
     */
    public function getCommissionApplyWhenOptions()
    {
        return [
            self::COMMISSION_APPLY_AFTER => 'After',
            self::COMMISSION_APPLY_BEFORE => 'Before',
        ];
    }
}
