<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items\Search;

/**
 * Class Grid
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items\Search
 */
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'sales/order_create/loadBlock',
            ['block' => 'search_grid', '_current' => true, 'collapse' => null]
        );
    }
}
