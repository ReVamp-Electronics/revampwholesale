<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Source\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Class Tabs
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses\Source\Edit
 */
class Tabs extends WidgetTabs
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('source_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Source Information'));
    }
}
