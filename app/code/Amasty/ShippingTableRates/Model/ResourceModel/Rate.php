<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel;

class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_table_rate', 'id');
    }

    public function deleteBy($methodId)
    {
        $this->getConnection()->delete($this->getMainTable(), 'method_id=' . intVal($methodId));
    }

    public function batchInsert($methodId, $data)
    {
        $err = '';

        $sql = '';
        for ($i = 0, $n = count($data); $i < $n; ++$i) {
            $sql .= ' (NULL,' . $methodId;
            foreach ($data[$i] as $v) {
                $sql .= ', "' . $v . '"';
            }
            $sql .= '),';
        }

        if ($sql) {

            $sql = 'INSERT INTO `' . $this->getMainTable() . '` VALUES ' . substr($sql, 0, -1);
            try {
                $this->getConnection()->query($sql);
            } catch (\Exception $e) {
                $err = $e->getMessage();
            }
        }

        return $err;
    }
}
