<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

namespace Amasty\Xsearch\Model\Indexer\Category;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;


class IndexStructure implements IndexStructureInterface
{
    protected $_resource;

    protected $_indexScopeResolver;

    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolver $indexScopeResolver
    ) {
        $this->_resource = $resource;
        $this->_indexScopeResolver = $indexScopeResolver;
    }

    public function delete($ind, array $dim = [])
    {
        $table = $this->_indexScopeResolver->resolve($ind, $dim);
        if ($this->_resource->getConnection()->isTableExists($table)) {
            $this->_resource->getConnection()->dropTable($table);
        }
    }

    public function create($index, array $fields, array $dimensions = [])
    {
        $this->createFulltextIndex($this->_indexScopeResolver->resolve($index, $dimensions));
    }

    protected function createFulltextIndex($tableName)
    {
        $table = $this->_resource->getConnection()->newTable($tableName)
           ->addColumn(
               'entity_id',
               Table::TYPE_INTEGER,
               10,
               ['unsigned' => true, 'nullable' => false],
               'Entity ID'
           )->addColumn(
                'data_index',
                Table::TYPE_TEXT,
                '4g',
                ['nullable' => true],
                'Data index'
            )->addColumn(
               'attribute_id',
               Table::TYPE_INTEGER,
               10,
               ['unsigned' => true, 'nullable' => false]
           )->addIndex(
               'idx_primary',
               ['entity_id', 'attribute_id'],
               ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
           )->addIndex(
               'FTI_FULLTEXT_DATA_INDEX',
               ['data_index'],
               ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
           );
       $this->_resource->getConnection()->createTable($table);
    }
}
