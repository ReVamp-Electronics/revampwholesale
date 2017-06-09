<?php

namespace MW\RewardPoints\Block\Adminhtml\Products\Import;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	public function _construct()
    {
    	$this->_objectId = 'product_import';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_products_import';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Import Product Reward Points'));
        $this->buttonList->remove('delete');
    }

    public function getHeaderText()
    {
    	return __('Import Product Reward Points');
    }
}
