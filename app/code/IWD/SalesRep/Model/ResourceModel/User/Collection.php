<?php

namespace IWD\SalesRep\Model\ResourceModel\User;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\ResourceModel\User
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\IWD\SalesRep\Model\User', '\IWD\SalesRep\Model\ResourceModel\User');
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->from('', 'COUNT(DISTINCT `main_table`.`entity_id`)');
        return $countSelect;
    }
}
