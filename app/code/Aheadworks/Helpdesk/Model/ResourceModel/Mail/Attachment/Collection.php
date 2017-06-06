<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment
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
        $this->_init('Aheadworks\Helpdesk\Model\Mail\Attachment', 'Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment');
    }

    /**
     * Add filter by mail id
     *
     * @param $mailId
     * @return $this
     */
    public function addFilterByMailId($mailId)
    {
        return $this->addFieldToFilter('mailbox_id', $mailId);
    }
}