<?php

namespace IWD\SalesRep\Model;

/**
 * Class B2BCustomer
 * @package IWD\SalesRep\Model
 */
class B2BCustomer extends \Magento\Framework\Model\AbstractModel
{
    const ENTITY_ID = 'entity_id';
    const SALESREP_ID = 'salesrep_id';
    const CUSTOMER_ID = 'customer_id';
    const WEBSITE_ID = 'website_id';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'salesrep_customer';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\ResourceModel\B2BCustomer');
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
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
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
     * @param $id
     * @return $this
     */
    public function setWebsiteId($id)
    {
        return $this->setData(self::WEBSITE_ID, $id);
    }
}
