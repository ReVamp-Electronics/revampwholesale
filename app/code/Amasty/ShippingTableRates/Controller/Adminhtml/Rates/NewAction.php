<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

class NewAction extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
