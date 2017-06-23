<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RelationDetails extends AbstractDb
{
    public function _construct()
    {
        $this->_init('amasty_customer_attributes_details', 'id');
    }

    /**
     * Delete Details data for relation
     *
     * @param int $relationId
     */
    public function deleteAllDetailForRelation($relationId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['relation_id = ?' => $relationId]);
    }

    public function fastDelete($ids)
    {
        $db    = $this->getConnection();
        $table = $this->getTable('amasty_customer_attributes_details');
        $db->delete($table, $db->quoteInto('id IN(?)', $ids));
    }
}
