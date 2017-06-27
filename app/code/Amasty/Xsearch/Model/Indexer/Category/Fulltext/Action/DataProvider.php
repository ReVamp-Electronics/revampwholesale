<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action;

use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider
{
    /**
     * Searchable attributes cache
     *
     * @var \Magento\Eav\Model\Entity\Attribute[]
     */
    private $searchableAttributes;

    /**
     * Index values separator
     *
     * @var string
     */
    private $separator = ' | ';

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Xsearch\Model\ResourceModel\Engine
     */
    private $engine;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

	/**
	 * @var \Magento\Framework\App\ProductMetadataInterface
	 */
	protected $productMetadata;

    private $actionFull;

    public function __construct(
        ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Amasty\Xsearch\Model\ResourceModel\EngineFactory $engineFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
	    $this->productMetadata = $productMetadata;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->eavConfig = $eavConfig;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->engine = $engineFactory->create();
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    private function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    public function getSearchableCategories(
        $storeId,
        $categoriesIds = null,
        $lastCategoryId = 0,
        $limit = 100
    ) {

        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        $select = $this->connection->select()
            ->from(
                ['e' => $this->getTable('catalog_category_entity')],
                ['entity_id']
            );

        if ($categoriesIds !== null) {
            $select->where('e.entity_id IN (?)', $categoriesIds);
        }

        $select->where('e.entity_id > ?', $lastCategoryId)->limit($limit)->order('e.entity_id');

        $result = $this->connection->fetchAll($select);

        return $result;
    }

    /**
     * Returns expression for field unification
     *
     * @param string $field
     * @param string $backendType
     * @return \Zend_Db_Expr
     */
    private function unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->connection->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    public function getCategoryAttributes($storeId, array $categoryIds, array $attributeTypes)
    {
        $result = [];
        $selects = [];
	    $edition = $this->productMetadata->getEdition();
	    if ($edition == "Community") {
		    $id = 'entity_id';
	    } elseif ($edition == "Enterprise") {
		    $id = 'row_id';
	    }
        $ifStoreValue = $this->connection->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable('catalog_category_entity_' . $backendType);
                $selects[] = $this->connection->select()->from(
                    ['t_default' => $tableName],
                    [$id, 'attribute_id']
                )->joinLeft(
                    ['t_store' => $tableName],
                    $this->connection->quoteInto(
                        't_default.' . $id . '=t_store.' . $id .
                        ' AND t_default.attribute_id=t_store.attribute_id' .
                        ' AND t_store.store_id = ?',
                        $storeId
                    ),
                    ['value' => $this->unifyField($ifStoreValue, $backendType)]
                )->where(
                    't_default.store_id = ?',
                    0
                )->where(
                    't_default.attribute_id IN (?)',
                    $attributeIds
                )->where(
                    't_default.' . $id . ' IN (?)',
                    $categoryIds
                );
            }
        }

        if ($selects) {

            $select = $this->connection->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);

            $query = $this->connection->query($select);
            while ($row = $query->fetch()) {
                $result[$row[$id]][$row['attribute_id']] = $row['value'];
            }
        }

        return $result;
    }

    public function setActionFull(Full $actionFull)
    {
        $this->actionFull = $actionFull;
    }

    public function prepareCategoryIndex($indexData, $categoryData, $storeId)
    {
        $index = [];

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {

                $value = $this->getAttributeValue($attributeId, $attributeValue, $storeId);

                if (!empty($value)) {
                    if (isset($index[$attributeId])) {
                        $index[$attributeId][$entityId] = $value;
                    } else {
                        $index[$attributeId] = [$entityId => $value];
                    }
                }
            }
        }

        return $this->engine->prepareEntityIndex($index, $this->separator);
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $valueId
     * @param int $storeId
     * @return string
     */
    private function getAttributeValue($attributeId, $valueId, $storeId)
    {
        $attribute = $this->actionFull->getSearchableAttribute($attributeId);
        $value = $this->engine->processAttributeValue($attribute, $valueId);

        if ( $value !== false && $attribute->usesSource()) {
            $attribute->setStoreId($storeId);

            $valueText = (array) $attribute->getSource()->getIndexOptionText($valueId);

            $pieces = array_filter(array_merge([$value], $valueText));

            $value = implode($this->separator, $pieces);
        }

        $value = preg_replace('/\\s+/siu', ' ', trim(strip_tags($value)));

        return $value;
    }
}
