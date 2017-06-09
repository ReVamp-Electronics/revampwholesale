<?php

namespace MW\RewardPoints\Block\Adminhtml\Catalogrules\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_rules_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Catalog Reward Rules'));
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
                    'MW\RewardPoints\Block\Adminhtml\Catalogrules\Edit\Tab\Form'
                )->toHtml()
            ]
        );

        $this->addTab(
            'form_conditions',
            [
                'label'     => __('Conditions'),
                'title'     => __('Conditions'),
                'content'   => $this->getLayout()->createBlock(
                    'MW\RewardPoints\Block\Adminhtml\Catalogrules\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );

        $this->addTab(
            'form_actions',
            [
                'label'     => __('Actions'),
                'title'     => __('Actions'),
                'content'   => $this->getLayout()->createBlock(
                    'MW\RewardPoints\Block\Adminhtml\Catalogrules\Edit\Tab\Actions'
                )->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}
