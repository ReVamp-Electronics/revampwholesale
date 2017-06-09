<?php

namespace IWD\MultiInventory\Observer;

use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use IWD\MultiInventory\Helper\Data;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class IwdOrdermanagerUpdateOrderItems
 * @package IWD\MultiInventory\Observer
 */
class IwdOrdermanagerUpdateOrderItems implements ObserverInterface
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
        if ($this->helper->isExtensionEnabled()) {
            $orderId = $observer->getEvent()->getOrderId();
            $this->multiStockManagement->loadOrder($orderId);
            $this->multiStockManagement->autoSyncStocks();
        }
    }
}
