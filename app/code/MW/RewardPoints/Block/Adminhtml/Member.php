<?php

namespace MW\RewardPoints\Block\Adminhtml;

class Member extends \Magento\Backend\Block\Widget\Grid\Container
{
	protected function _construct()
    {
        $this->_controller = 'adminhtml_member';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('Customer Accounts');

        parent::_construct();

        $this->buttonList->remove('add');
        $this->buttonList->add(
            'import',
            [
                'label' => __('Import Reward Points'),
                'class' => 'add',
                'onclick' => 'setLocation("' . $this->getUrl('*/rewardpoints/import') .'")',
            ],
            -100
        );
    }
}
