<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Render;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Name extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $name = $row->getData($this->getColumn()->getIndex());
        $url = $this->getUrl(
            'catalog/product/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
        return '<a href="' . $url . '" target="_blank">' . $name . '</a>';
    }
}
