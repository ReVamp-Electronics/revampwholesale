<?php

namespace IWD\SalesRep\Block\Adminhtml\Reports;

/**
 * Class Order
 * @package IWD\SalesRep\Block\Adminhtml\Reports
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'IWD_SalesRep';
        $this->_controller = 'adminhtml_reports_order';
        $this->_headerText = __('Sales Rep Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }
}
