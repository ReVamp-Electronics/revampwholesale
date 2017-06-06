<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Data;

use Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class DepartmentStoreLabel
 * @package Aheadworks\Helpdesk\Model\Data
 * @codeCoverageIgnore
 */
class DepartmentStoreLabel extends AbstractSimpleObject implements DepartmentStoreLabelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
