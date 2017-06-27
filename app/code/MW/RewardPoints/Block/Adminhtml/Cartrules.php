<?php

namespace MW\RewardPoints\Block\Adminhtml;

class Cartrules extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_cartrules';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('Shopping Cart Earning Rule');
        $this->_addButtonLabel = __('Add New Rule');

        parent::_construct();
    }
}
