<?php

namespace MW\RewardPoints\Block\Adminhtml;

class Spendcartrules extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller     = 'adminhtml_spendcartrules';
        $this->_blockGroup     = 'MW_RewardPoints';
        $this->_headerText     = __('Reward Point Spending Rules');

        parent::_construct();

        $this->_addButtonLabel = __('Add New Rule');
    }
}
