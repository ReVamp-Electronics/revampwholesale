<?php
namespace Evdpl\Jobopening\Block\Adminhtml;

class Jobopening extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_jobopening';
        $this->_blockGroup = 'Evdpl_Jobopening';
        $this->_headerText = __('Job opening');
        $this->_addButtonLabel = __('Add Job opening');
        parent::_construct();
        if ($this->_isAllowedAction('Evdpl_Jobopening::save')) {
            $this->buttonList->update('add', 'label', __('Add Job opening'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
