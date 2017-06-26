<?php
namespace Evdpl\Ourteam\Block\Adminhtml;

class Post extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'Evdpl_Ourteam';
        $this->_headerText = __('Manage Our Team Members');

        parent::_construct();

        if ($this->_isAllowedAction('Evdpl_Ourteam::save')) {
            $this->buttonList->update('add', 'label', __('Add New Team Member'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
