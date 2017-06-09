<?php

namespace IWD\SalesRep\Block\Adminhtml\Commission;

/**
 * Class Edit
 * @package IWD\SalesRep\Block\Adminhtml\Commission
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_template = 'IWD_SalesRep::widget/form/container.phtml';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'IWD_SalesRep';
        $this->_controller = 'adminhtml_commission';

        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'id', 'iwdsr-save-commission');
    }
}
