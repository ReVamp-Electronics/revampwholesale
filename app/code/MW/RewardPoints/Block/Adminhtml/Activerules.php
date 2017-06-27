<?php

namespace MW\RewardPoints\Block\Adminhtml;

class Activerules extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_activerules';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('Manage Customer Behavior Rules');

        parent::_construct();
    }
}
