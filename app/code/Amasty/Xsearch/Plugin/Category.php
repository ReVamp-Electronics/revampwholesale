<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

namespace Amasty\Xsearch\Plugin;

class Category
{
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ){
        $this->indexerRegistry = $indexerRegistry;
    }

    public function afterReindex(\Magento\Catalog\Model\Category $category, $result)
    {
        $indexer = $this->indexerRegistry->get(\Amasty\Xsearch\Model\Indexer\Category\Fulltext::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexList($category->getPathIds());
        }

        return $result;
    }
}