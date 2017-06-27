<?php

namespace MW\RewardPoints\Block\Adminhtml\Products;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_controller = 'adminhtml_products';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Product Reward Points'));
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
        $this->buttonList->add(
            'import',
            [
                'label' => __('Import Product Reward Points'),
                'class' => 'add',
                'onclick' => 'setLocation("' . $this->getUrl('*/*/importProductPoints') .'")',
            ],
            -100
        );
    }
}
