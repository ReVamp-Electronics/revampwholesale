<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel;

/**
 * Class Config
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_config', 'id');
    }

}