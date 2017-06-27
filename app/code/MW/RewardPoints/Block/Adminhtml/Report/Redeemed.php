<?php

namespace MW\RewardPoints\Block\Adminhtml\Report;

class Redeemed extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_report_redeemed';
        $this->_headerText = __('Redeemed Points');
        $this->_blockGroup = 'MW_RewardPoints';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
