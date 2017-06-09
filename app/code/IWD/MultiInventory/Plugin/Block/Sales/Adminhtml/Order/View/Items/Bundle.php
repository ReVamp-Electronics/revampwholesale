<?php

namespace IWD\MultiInventory\Plugin\Block\Sales\Adminhtml\Order\View\Items;

use Magento\Backend\Block\Template;
use IWD\MultiInventory\Helper\Data;

/**
 * Class Bundle
 * @package IWD\MultiInventory\Plugin\Block\Sales\Adminhtml\Order\View\Items
 */
class Bundle
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Template $originalBlock
     */
    public function beforeToHtml(Template $originalBlock)
    {
        if ($this->helper->isExtensionEnabled()) {
            $originalBlock->setTemplate('IWD_MultiInventory::order/view/items/renderer/bundle.phtml');
        }
    }
}
