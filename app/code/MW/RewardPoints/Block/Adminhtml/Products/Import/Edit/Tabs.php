<?php

namespace MW\RewardPoints\Block\Adminhtml\Products\Import\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rewardpoints_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Product Reward Points'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'mw_rewardpoints_form_section',
            [
                'label' => __('Import Product Reward Points'),
                'title' => __('Import Product Reward Points'),
                'content' => $this->getLayout()->createBlock(
                	'MW\RewardPoints\Block\Adminhtml\Products\Import\Edit\Tab\Form'
                )->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}
