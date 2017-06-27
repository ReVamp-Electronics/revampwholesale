<?php

namespace IWD\SalesRep\Block\Adminhtml;

use \IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class Salesrep
 * @package IWD\SalesRep\Block\Adminhtml
 */
class Salesrep extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_salesrep';
        $this->_blockGroup = 'IWD_SalesRep';
        $this->_headerText = __('Sales Representatives');
        $this->_addButtonLabel = __('Add New User');
        parent::_construct();

        $url = $this->getUrl('adminhtml/user/new', [SalesrepHelper::HTTP_REFERRER_KEY => SalesrepHelper::HTTP_REFERRER]);
        $this->buttonList->update(
            'add',
            'onclick',
            'setLocation("' . $url . '")'
        );
    }
}
