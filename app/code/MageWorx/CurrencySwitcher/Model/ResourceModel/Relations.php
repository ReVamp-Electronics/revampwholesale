<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Model\ResourceModel;

use MageWorx\CurrencySwitcher\Model\Relations as ModelRelations;

/**
 * Relations resource model
 */
class Relations extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelRelations::ENTITY, ModelRelations::KEY_RELATION_ID);
    }
}
