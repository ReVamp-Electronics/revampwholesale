<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel\Mail;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Mail
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\Mail', 'Aheadworks\Helpdesk\Model\ResourceModel\Mail');
    }

    /**
     * Add gateway filter
     *
     * @return $this
     */
    public function addGatewayFilter()
    {
        $this->addFilter('type', ['eq' => \Aheadworks\Helpdesk\Model\Mail::TYPE_FROM_GATEWAY], 'public');
        return $this;
    }

    /**
     * Add unprocessed filter
     *
     * @return $this
     */
    public function addUnprocessedFilter()
    {
        $this->addFilter('status', ['eq' => \Aheadworks\Helpdesk\Model\Mail::STATUS_UNPROCESSED], 'public');
        return $this;
    }
}