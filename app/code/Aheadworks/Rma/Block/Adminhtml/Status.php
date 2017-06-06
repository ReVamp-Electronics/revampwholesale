<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml;

class Status extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_blockGroup = 'Aheadworks_Rma';
        $this->_controller = 'adminhtml_status';
        $this->_headerText = __('Statuses and Email Templates');
        parent::_construct();
        $this->removeButton('add');
    }
}
