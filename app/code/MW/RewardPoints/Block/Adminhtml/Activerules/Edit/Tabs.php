<?php

namespace MW\RewardPoints\Block\Adminhtml\Activerules\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rules_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Customer Behavior Rule'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_rules_detail',
            [
                'label'   => __('General information'),
                'title'   => __('General information'),
                'content' => $this->getLayout()->createBlock(
                    'MW\RewardPoints\Block\Adminhtml\Activerules\Edit\Tab\Form'
                )->toHtml(),
            ]
        );

        return parent::_beforeToHtml();
    }
}
