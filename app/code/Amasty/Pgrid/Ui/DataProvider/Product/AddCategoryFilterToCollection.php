<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Pgrid\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class AddQuantityFieldToCollection
 */
class AddCategoryFilterToCollection implements AddFilterToCollectionInterface
{

    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq']) && $condition['eq'] == 0){
            $categorySelect = $collection->getConnection()->select();

            $categorySelect->joinLeft(array('nocat_idx' => $collection->getTable('catalog_category_product_index')),
                '(nocat_idx.product_id = e.entity_id)',
                array(
                    'nocat_idx.product_id',
                )
            );
            $categorySelect->where('nocat_idx.category_id IS NULL');

            $selectCondition = [
                'in' => $categorySelect
            ];

            $collection->getSelect()->where($collection->getConnection()->prepareSqlCondition('e.entity_id' , $selectCondition));
        } else {
            $collection->addCategoriesFilter($condition);
        }
    }
}
