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
namespace Amasty\Xsearch\Model\Adapter\Category\Mysql\Aggregation;



use Magento\Framework\App\ResourceConnection;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $currentScope = $dimensions['scope']->getValue();

        $select = $this->getSelect();

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
