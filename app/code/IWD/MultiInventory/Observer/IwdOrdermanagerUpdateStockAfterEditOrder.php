<?php

namespace IWD\MultiInventory\Observer;

use IWD\MultiInventory\Helper\Data;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class IwdOrdermanagerAdditionalUpdateLastStep
 * @package IWD\MultiInventory\Observer
 */
class IwdOrdermanagerUpdateStockAfterEditOrder implements ObserverInterface
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
     * @var \IWD\OrderManager\Controller\Adminhtml\Order\Additional\AbstractAction
     */
    private $additional;

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
        $this->additional = $observer->getEvent()->getData('additional');

        if ($this->needUpdateStocks()) {
            $form = $this->formForUpdateStocks();
            $this->additional->setFormAfterReAuthorization($form);
        }
    }

    /**
     * @return bool
     */
    private function needUpdateStocks()
    {
        if ($this->helper->isExtensionEnabled()) {
            $orderId = $this->getOrderId();
            $this->multiStockManagement->loadOrder($orderId);
            return $this->multiStockManagement->needUpdateStocks();
        }

        return false;
    }

    /**
     * @return string[]|bool
     * @throws \Exception
     */
    private function formForUpdateStocks()
    {
        $orderId = $this->getOrderId();
        $this->multiStockManagement->loadOrder($orderId);

        $orderItems = $this->multiStockManagement->getOrderItems();
        $stockList = $this->multiStockManagement->getStocksList();
        if (empty($orderItems) || empty($stockList)) {
            return false;
        }

        return [
            'result' => 'multistock',
            'id' => $this->getOrderId(),
            'reloadPage' => true,
            'order_items' => $orderItems,
            'stocks' => $stockList
        ];
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->additional->getOrderId();
    }
}
