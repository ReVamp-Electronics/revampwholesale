<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Purchases;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Items
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Purchases
 */
class Items implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Block template path
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::ticket/edit/tabs/purchases.phtml';

    /**
     * Template Block
     *
     * @var \Magento\Backend\Block\Template
     */
    protected $block;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template $block
     */
    public function __construct(
        \Magento\Backend\Block\Template $block
    ) {
        $this->block = $block;
    }

    /**
     * Render element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = "<div class='admin__field field field-purchased-order_items'>";
        $html .=  $this->block
            ->setOrderCollection($element->getOrderCollection())
            ->setTemplate($this->_template)
            ->toHtml();
        $html .= "</div>";
        return $html;
    }
}
