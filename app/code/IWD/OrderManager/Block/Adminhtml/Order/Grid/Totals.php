<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Grid;

use Magento\Backend\Block\Template;

class Totals extends Template
{
    /**
     * @return bool
     */
    public function isGridTotalsEnabled()
    {
        return (bool)$this->_scopeConfig->getValue('iwdordermanager/order_grid/order_grid_enable');
    }
}
