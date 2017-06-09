<?php

namespace IWD\MultiInventory\Block\Adminhtml\Order\Items\Stock;

use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;

/**
 * Class Renderer
 * @package IWD\MultiInventory\Block\Adminhtml\Order\Items\Stock
 */
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
