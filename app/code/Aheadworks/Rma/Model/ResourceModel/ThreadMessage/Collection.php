<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Model\ResourceModel\ThreadMessage;

/**
 * Class Collection
 * @package Aheadworks\Rma\Model\ResourceModel\ThreadMessage
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\ThreadMessage', 'Aheadworks\Rma\Model\ResourceModel\ThreadMessage');
    }

    /**
     * @param $requestId
     * @return $this
     */
    public function getRequestThread($requestId)
    {
        $this->addFieldToFilter('request_id', $requestId);
        return $this;
    }
}