<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

/**
 * Class Attachment
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class Attachment extends AbstractResource
{
    protected function _construct()
    {
        $this->_init('aw_rma_thread_attachment', 'id');
    }
}