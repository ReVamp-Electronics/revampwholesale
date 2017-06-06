<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel;

/**
 * Class Department
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Department extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_department', 'id');
    }
}
