<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Model\ResourceModel\Attachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\Attachment', 'Aheadworks\Rma\Model\ResourceModel\Attachment');
    }
}