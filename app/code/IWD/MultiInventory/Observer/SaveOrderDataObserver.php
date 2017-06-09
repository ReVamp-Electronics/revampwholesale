<?php

namespace IWD\MultiInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use IWD\MultiInventory\Ui\Component\Listing\Column\Stock\Options as ColumnStockOptions;

/**
 * Class SaveOrderDataObserver
 * @package IWD\MultiInventory\Observer
 */
class SaveOrderDataObserver implements ObserverInterface
{
    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @param MultiStockManagement $multiStockManagement
     */
    public function __construct(MultiStockManagement $multiStockManagement) {
        $this->multiStockManagement = $multiStockManagement;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $this->multiStockManagement->setOrder($order);
        $ordered = $this->multiStockManagement->getOrderQtyOrdered();
        $assigned = $this->multiStockManagement->getOrderQtyAssigned();

        $stockAssigned = ($ordered == 0)
            ? ColumnStockOptions::NOT_APPLICABLE
            : ($ordered == $assigned
                ? ColumnStockOptions::ASSIGNED
                : ColumnStockOptions::NOT_ASSIGNED);

        $order->setIwdStockAssigned($stockAssigned);
    }
}
