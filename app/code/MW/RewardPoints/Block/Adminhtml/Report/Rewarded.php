<?php

namespace MW\RewardPoints\Block\Adminhtml\Report;

class Rewarded extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_report_rewarded';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('Rewarded Points');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
