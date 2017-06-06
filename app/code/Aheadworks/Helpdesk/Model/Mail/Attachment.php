<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Mail;

/**
 * Class Attachment
 * @package Aheadworks\Helpdesk\Model\Mail
 */
class Attachment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment');
    }
}