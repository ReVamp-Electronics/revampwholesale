<?php

namespace MW\RewardPoints\Block\Adminhtml\Report;

class Overview extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_report_overview';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('Report Overview');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
