<?php

namespace MW\RewardPoints\Block\Adminhtml\Report;

class Dashboard extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_report_dashboard';
        $this->_headerText = __('Dashboard');
        $this->_blockGroup = 'MW_RewardPoints';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
