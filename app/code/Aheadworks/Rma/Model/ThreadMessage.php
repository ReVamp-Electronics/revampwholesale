<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;

/**
 * Class ThreadMeaasge
 * @package Aheadworks\Rma\Model
 */
class ThreadMessage extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\ResourceModel\ThreadMessage');
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getOwnerType() == Owner::ADMIN_VALUE;
    }

    /**
     * @return bool
     */
    public function isCustomer()
    {
        return $this->getOwnerType() == Owner::CUSTOMER_VALUE;
    }

    /**
     * @return string
     */
    public function getOwnerName()
    {
        if ($this->getId() && !$this->hasData('owner_name')) {
            $this->getResource()->attachOwnerName($this);
        }
        return $this->getData('owner_name');
    }

    /**
     * @param null $index
     * @return mixed
     */
    public function getAttachments($index = null)
    {
        if ($this->getId() && !$this->hasData('attachments')) {
            $this->getResource()->attachAttachmentsData($this);
        }
        return $this->getData('attachments', $index);
    }
}