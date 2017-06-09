<?php

namespace MW\RewardPoints\Block\Adminhtml;

class Catalogrules extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_catalogrules';
        $this->_blockGroup = 'MW_RewardPoints';
        $this->_headerText = __('Catalog Reward Rules');
        $this->_addButtonLabel = __('Add New Rule');
        $this->buttonList->add(
            'apply_rules',
            [
                'label' => __('Apply Rules'),
                'class' => 'add',
                'onclick' => 'setLocation("' . $this->getUrl('*/*/applyRules') .'")',
            ],
            -100
        );

        parent::_construct();
    }
}
