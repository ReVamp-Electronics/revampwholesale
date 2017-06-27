<?php

namespace IWD\SalesRep\Model;

use IWD\SalesRep\Api\Data\UserInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class User
 * @package IWD\SalesRep\Model
 */
class User extends AbstractModel implements UserInterface
{
    /**
     * @inheritdoc
     */
    protected $_eventPrefix = 'salesrep_user';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\ResourceModel\User');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::SALESREP_ID);
    }

    /**
     * @inheritdoc
     */
    public function getEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function getAdminId()
    {
        return $this->getData(self::ADMIN_ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::SALESREP_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setEnabled($enabled)
    {
        return $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @inheritdoc
     */
    public function setAdminId($adminId)
    {
        return $this->setData(self::ADMIN_ID, $adminId);
    }
}
