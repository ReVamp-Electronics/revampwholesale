<?php

namespace MW\RewardPoints\Block\Adminhtml\Rewardpoints\Edit;

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
        $this->setTitle(__('Manage Reward Points'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
    	$this->addTab(
    		'form_section',
    		[
    			'label'     => __('Import Reward Points'),
				'title'     => __('Import Reward Points'),
				'content'   => $this->getLayout()->createBlock(
					'MW\RewardPoints\Block\Adminhtml\Rewardpoints\Edit\Tab\Form'
				)->toHtml()
    		]
		);

    	return parent::_beforeToHtml();
    }
}
