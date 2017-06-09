<?php

namespace IWD\SalesRep\Model\Preference\ResourceModel\User;

/**
 * Class User
 * @package IWD\SalesRep\Model\Preference\ResourceModel\User
 */
class User extends \Magento\User\Model\ResourceModel\User
{
    const FIELD_NAME_SALESREPID = 'iwd_salesrep_id';
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    private $deploymentConfig;

    /**
     * @inheritdoc
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->resource === null) {
            $this->resource = $om->get('\Magento\Framework\App\ResourceConnection');
        }

        if ($this->deploymentConfig === null) {
            $this->deploymentConfig = $om->get('\Magento\Framework\App\DeploymentConfig');
        }

        $prefix = $this->deploymentConfig->get(\Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX);
        
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(
            ['salesrep' => $this->resource->getTableName(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME)],
            $prefix.'admin_user.user_id = salesrep.' . \IWD\SalesRep\Model\User::ADMIN_ID,
            [ self::FIELD_NAME_SALESREPID => \IWD\SalesRep\Model\User::SALESREP_ID ]
        );

        return $select;
    }
}
