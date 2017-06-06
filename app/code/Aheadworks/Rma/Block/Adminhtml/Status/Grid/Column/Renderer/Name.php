<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Status\Grid\Column\Renderer;

/**
 * Class Name
 * @package Aheadworks\Rma\Block\Adminhtml\Status\Grid\Column\Renderer
 */
class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $attributes = new \Magento\Framework\DataObject([
            'href' => $this->getUrl('*/*/edit', ['id' => $row->getId()])
        ]);
        return '<a ' . $attributes->serialize() . ' >' . $this->escapeHtml($row->getName()) . '</a>';
    }
}
