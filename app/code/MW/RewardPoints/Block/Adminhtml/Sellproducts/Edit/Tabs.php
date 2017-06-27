<?php

namespace MW\RewardPoints\Block\Adminhtml\Sellproducts\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('products_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Sell Products in Points'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'mw_rewardpoints_form_products',
            [
                'label' => __('Products'),
                'title' => __('Products'),
                'content' => $this->getLayout()->createBlock(
                	'MW\RewardPoints\Block\Adminhtml\Sellproducts\Edit\Tab\Grid'
                )->toHtml() . $this->getLayout()->createBlock(
                	'MW\RewardPoints\Block\Adminhtml\Sellproducts\Edit\Tab\Headjs'
                )->setTemplate('MW_RewardPoints::sellproducts/edit/tab/headjs.phtml')
                ->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}
