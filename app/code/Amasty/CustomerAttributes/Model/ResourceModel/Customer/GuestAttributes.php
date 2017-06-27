<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\ResourceModel\Customer;

class GuestAttributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * types for add fields in mysql
     */
    protected $eavTypes
        = [
            "varchar" => "varchar(255)",
            "tier_price" => "decimal(12,4)",
            "text" => "text",
            "media_gallery_value" => "varchar(255)",
            "media_gallery" => "varchar(255)",
            "int" => "int(11)",
            "group_price" => "decimal(12,4)",
            "gallery" => "varchar(255)",
            "decimal" => "decimal(12,4)",
            "datetime" => "datetime",
            "static" => "varchar(255)"
        ];

    /**
     * add fields to amcustomerattr/guest
     * with the appropriate $types
     */
    public function addFields($fields, $types)
    {
        if (empty($fields)) {
            return;
        }

        $tableName = $this->getMainTable();
        $eavTypes = $this->eavTypes;
        $columnsStr = implode(
            ',',
            array_map(
                function ($field) use ($types, $eavTypes) {
                    return "ADD COLUMN `$field` {$eavTypes[$types[$field]]} ";
                },
                $fields
            )
        );
        $sql = "ALTER TABLE `" . $tableName . "` " . $columnsStr;
        $this->getConnection()->query($sql);
        $this->getConnection()->resetDdlCache();
    }

    /**
     * delete $fields from amcustomerattr/guest
     */
    public function deleteFields($fields)
    {
        if (empty($fields)) {
            return;
        }

        $tableName = $this->getMainTable();
        $columnsStr = implode(
            ',',
            array_map(
                function ($field) {
                    return "DROP COLUMN `$field`";
                },
                $fields
            )
        );
        $sql = "ALTER TABLE `" . $tableName . "` " . $columnsStr;
        $this->getConnection()->query($sql);
        $this->getConnection()->resetDdlCache();
    }

    public function getFields()
    {
        $tableName = $this->getMainTable();
        $fieldObject = $this->getConnection()->describeTable($tableName);
        $fields = [];
        foreach ($fieldObject as $name => $value) {
            if (!in_array($name, ['id', 'order_id'])) {
                $fields[] = $name;
            }
        }

        return $fields;
    }

    public function loadByOrderId(\Magento\Framework\Model\AbstractModel $object, $orderId)
    {
        $connection = $this->getConnection();
        if ($connection && $orderId !== null) {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable(),
                '*'
            )
                ->where($this->getMainTable() . '.order_id = ?', $orderId);

            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    protected function _construct()
    {
        $this->_init('amasty_customer_attributes_guest', 'id');
        $this->getUniqueFields();
    }
}
