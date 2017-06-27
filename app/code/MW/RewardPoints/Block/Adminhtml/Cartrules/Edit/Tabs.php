<?php

namespace MW\RewardPoints\Block\Adminhtml\Cartrules\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cart_rules_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Shopping Cart Earning Rule'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_program_detail',
            [
                'label'     => __('Rule information'),
                'title'     => __('Rule information'),
                'content'   => $this->getLayout()->createBlock(
                    'MW\RewardPoints\Block\Adminhtml\Cartrules\Edit\Tab\Form'
                )->toHtml(),
                'active'  => true,
            ]
        );

        $this->addTab(
            'form_conditions',
            [
                'label'     => __('Conditions'),
                'title'     => __('Conditions'),
                'content'   => $this->getLayout()->createBlock(
                    'MW\RewardPoints\Block\Adminhtml\Cartrules\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );

        $this->addTab(
            'form_actions',
            [
                'label'     => __('Actions'),
                'title'     => __('Actions'),
                'content'   => $this->getLayout()->createBlock(
                    'MW\RewardPoints\Block\Adminhtml\Cartrules\Edit\Tab\Actions'
                )->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}
