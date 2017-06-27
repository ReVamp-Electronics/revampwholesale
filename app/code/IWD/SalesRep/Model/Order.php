<?php

namespace IWD\SalesRep\Model;

/**
 * Class Order
 * @package IWD\SalesRep\Model
 */
class Order extends \Magento\Framework\Model\AbstractModel
{
    const ENTITY_ID = 'entity_id';
    const SALESREP_ID = 'salesrep_id';
    const ORDER_ID = 'order_id';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'salesrep_attached_order';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\ResourceModel\Order');
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
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function setOrderId($customerId)
    {
        return $this->setData(self::ORDER_ID, $customerId);
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
}
