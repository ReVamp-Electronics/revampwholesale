<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items\Stock;

use \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;

class Renderer extends DefaultRenderer
{
    /**
     * @param mixed $item
     * @return string
     */
    public function getValueHtml($item)
    {
        return 0;
    }
}
