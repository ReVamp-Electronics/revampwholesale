<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Create\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Attachment
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Create\Renderer
 */
class Attachment implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Block template path
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::ticket/create/attachment.phtml';

    /**
     * Template Block
     *
     * @var \Magento\Backend\Block\Template
     */
    protected $block;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template $block
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Backend\Block\Template $block,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
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
        $html = "<div class='admin__field field field-order_items'>";
        $html .= $this->getBlockHtml();
        $html .= "</div>";
        return $html;
    }

    /**
     * Get block html
     *
     * @param $orderModel
     * @return mixed
     */
    public function getBlockHtml()
    {
        $url = $this->urlBuilder->getUrl('*/*/upload');
        return $this->block
            ->setFileUploadUrl($url)
            ->setTemplate($this->_template)
            ->toHtml();
    }
}
