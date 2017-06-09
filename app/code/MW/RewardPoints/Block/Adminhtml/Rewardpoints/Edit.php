<?php

namespace MW\RewardPoints\Block\Adminhtml\Rewardpoints;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	public function _construct()
    {
    	$this->_objectId = 'id';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_rewardpoints';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('delete');
        $this->buttonList->update('back', 'onclick', "setLocation('" . $this->getUrl('*/member/') . "')");
    }

    public function getHeaderText()
    {
    	return __('Import Reward Points');
    }
}
