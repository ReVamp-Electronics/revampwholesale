<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

namespace Amasty\Xsearch\Model\ResourceModel\Page\Fulltext;

use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
    /** @var  QueryResponse */
    protected $queryResponse;

    /** @var string */
    private $queryText;


    protected $_storeId;

    protected $weights = [
       'title' => 3,
       'content' => 2
    ];

    public function addSearchFilter($query)
    {
       $this->queryText = trim($this->queryText . ' ' . $query);
       return $this;
    }

    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }
        return $this->_storeId;
    }

    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }


    protected function getFulltextIndexColumns($collection, $indexTable)
    {
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                return $index['COLUMNS_LIST'];
            }
        }
        return [];
    }

    protected function _renderFiltersBefore()
    {
       $columns = $this->getFulltextIndexColumns($this, $this->getMainTable());

       $this->getSelect()
           ->where(
               'MATCH(' . implode(',', $columns) . ') AGAINST(?)',
               $this->queryText
           )->order(new \Zend_Db_Expr($this->getConnection()->quoteInto('MATCH(' . implode(',', $columns) . ') AGAINST(?)', $this->queryText) . ' desc'));

       return parent::_renderFiltersBefore();
    }
}