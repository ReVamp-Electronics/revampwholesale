<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Controller\Adminhtml\Relation;

class NewAction extends \Amasty\CustomerAttributes\Controller\Adminhtml\Relation
{
    /**
     * @return void
     */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
