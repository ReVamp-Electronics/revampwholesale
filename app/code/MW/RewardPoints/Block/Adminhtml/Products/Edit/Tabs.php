<?php

namespace MW\RewardPoints\Block\Adminhtml\Products\Edit;

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
        $this->setTitle(__('Individual Reward Points'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $commentText = '<div style="width: 100%; font-weight: bold; font-size: 13px; color: #EB5E00">' . __('Reward Points for products take priority over catalog rules. (Shopping cart rules may still apply)') . '</div>';

        $this->addTab(
            'mw_rewardpoints_form_products',
            [
                'label' => __('Products'),
                'title' => __('Products'),
                'content' => $commentText . $this->getLayout()->createBlock(
                	'MW\RewardPoints\Block\Adminhtml\Products\Edit\Tab\Grid'
                )->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}
