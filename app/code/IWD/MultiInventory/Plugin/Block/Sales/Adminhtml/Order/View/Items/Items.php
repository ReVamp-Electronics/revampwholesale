<?php

namespace IWD\MultiInventory\Plugin\Block\Sales\Adminhtml\Order\View\Items;

use Magento\Backend\Block\Template;
use IWD\MultiInventory\Helper\Data;

/**
 * Class Items
 * @package IWD\MultiInventory\Plugin\Block\Sales\Adminhtml\Order\View\Items
 */
class Items
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
     * @param $after
     * @return array
     */
    public function afterGetColumns(Template $originalBlock, $after)
    {
        if ($this->helper->isExtensionEnabled()) {
            $position = 6;
            $after = array_slice($after, 0, $position, true) +
                ['assign-stock' => "Assign Stock"] +
                array_slice($after, $position, count($after) - 1, true);
        }

        return $after;
    }
}
