<?php

namespace MW\RewardPoints\Block\Adminhtml\Sellproducts;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_sellproducts';

        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
    }
}
