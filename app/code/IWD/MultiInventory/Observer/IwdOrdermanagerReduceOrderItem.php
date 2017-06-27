<?php

namespace IWD\MultiInventory\Observer;

use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use IWD\MultiInventory\Helper\Data;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class IwdOrdermanagerReduceOrderItem
 * @package IWD\MultiInventory\Observer
 */
class IwdOrdermanagerReduceOrderItem implements ObserverInterface
{
    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @var Data
     */
    private $helper;

    /**
     * IwdOrdermanagerReduceOrderItem constructor.
     * @param MultiStockManagement $multiStockManagement
     * @param Data $helper
     */
    public function __construct(
        MultiStockManagement $multiStockManagement,
        Data $helper
    ) {
        $this->multiStockManagement = $multiStockManagement;
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var $qty int
         */
        $qty = $observer->getEvent()->getQty();

        /**
         * @var $orderItem \Magento\Sales\Api\Data\OrderItemInterface
         */
        $orderItem = $observer->getEvent()->getOrderItem();

        if ($qty == 0 && $this->helper->isExtensionEnabled()) {
            $this->multiStockManagement->backToInventory($orderItem->getItemId(), $orderItem->getProductId());
        }
    }
}
