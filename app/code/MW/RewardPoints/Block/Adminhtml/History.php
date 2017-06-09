<?php

namespace MW\RewardPoints\Block\Adminhtml;

class History extends \Magento\Backend\Block\Widget\Grid\Container
{
	protected function _construct()
    {
        $this->_controller = 'adminhtml_history';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('All Transaction History');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
