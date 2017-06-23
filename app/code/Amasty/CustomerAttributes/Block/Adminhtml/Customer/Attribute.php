<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Block\Adminhtml\Customer;

class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_customer_attribute';
        $this->_blockGroup = 'Amasty_CustomerAttributes';
        $this->_headerText = __('Customer Attributes');
        $this->_addButtonLabel = __('Add New Attribute');

        parent::_construct();
    }
}
