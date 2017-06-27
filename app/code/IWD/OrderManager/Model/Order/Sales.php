<?php

namespace IWD\OrderManager\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Model\AbstractModel;
use \IWD\OrderManager\Model\Config\Source\Shipments\UpdateMode;

/**
 * Class Sales
 * @package IWD\OrderManager\Model\Order
 */
class Sales extends AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * @var \IWD\OrderManager\Model\Invoice\Invoice
     */
    private $invoice;

    /**
     * @var \IWD\OrderManager\Model\Creditmemo\Creditmemo
     */
    private $creditmemo;

    /**
     * @var \IWD\OrderManager\Model\Shipment\Shipment
     */
    private $shipment;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    private $shipmentLoader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\OrderManager\Model\Invoice\Invoice $invoice
     * @param \IWD\OrderManager\Model\Shipment\Shipment $shipment
     * @param \IWD\OrderManager\Model\Creditmemo\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\OrderManager\Model\Invoice\Invoice $invoice,
        \IWD\OrderManager\Model\Shipment\Shipment $shipment,
        \IWD\OrderManager\Model\Creditmemo\Creditmemo $creditmemo,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->shipment = $shipment;
        $this->scopeConfig = $scopeConfig;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentLoader = $shipmentLoader;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function updateSalesObjects()
    {
        try {
            $order = $this->getOrder();

            if (!$order->isTotalWasChanged() && !$order->hasChangesInAmounts()
                && !$order->hasItemsWithIncreasedQty() && !$order->hasAddedItems()
                && !$order->hasItemsWithDecreasedQty() && !$order->hasRemovedItems()
            ) {
                return true;
            }

            if ($order->hasCreditmemos()) {
                $this->updateCreditMemos();
            } else {
                $this->updateInvoices();
            }

            $this->updateShipments();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function updateInvoices()
    {
        if ($this->getOrder()->hasInvoices()) {
            if ($this->isOrderTotalIncreased() && $this->allowKeepPrevInvoice()) {
                $this->createInvoiceForOrder();
            } else {
                $this->removeAllInvoices();
                $this->createInvoiceForOrder();
            }
        }
    }

    /**
     * @return bool
     */
    private function isOrderTotalIncreased()
    {
        $order = $this->getOrder();
        return ($order->hasItemsWithIncreasedQty() || $order->hasAddedItems())
            && (!$order->hasItemsWithDecreasedQty() && !$order->hasRemovedItems());
    }

    /**
     * @return string
     */
    private function allowKeepPrevInvoice()
    {
        return $this->scopeConfig->getValue('iwdordermanager/update_reauthorize/invoice_update_mode') == 'add';
    }

    /**
     * @return void
     */
    private function updateCreditMemos()
    {
        $this->removeAllCreditMemos();
        $this->updateInvoices();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function updateShipments()
    {
        $order = $this->getOrder();

        if ($order->hasShipments()) {
            switch ($this->getUpdateShipmentsMode()) {
                case UpdateMode::MODE_UPDATE_ADD:
                    if (!$this->isOrderTotalIncreased()) {
                        $this->removeAllShipments();
                    }
                    $this->createShipmentForOrder();
                    break;
                case UpdateMode::MODE_UPDATE_REBUILD:
                    $this->removeAllShipments();
                    $this->createShipmentForOrder();
                    break;
                case UpdateMode::MODE_UPDATE_NOTHING:
                    if ($order->hasRemovedItems()
                        || $order->hasItemsWithDecreasedQty()
                    ) {
                        $this->removeAllShipments();
                    }
                    break;
            }
        }
    }

    /**
     * @return string
     */
    private function getUpdateShipmentsMode()
    {
        return $this->scopeConfig->getValue('iwdordermanager/update_reauthorize/shipments_update_mode');
    }

    /**
     * @return void
     */
    private function removeAllCreditMemos()
    {
        /**
         * @var \Magento\Sales\Model\Order\Creditmemo $creditMemos
         */
        $creditMemos = $this->getOrder()->getCreditmemosCollection();
        foreach ($creditMemos as $creditMemo) {
            $creditMemo->delete();
        }

        $items = $this->getOrder()->getItems();
        foreach ($items as $item) {
            $item->setQtyRefunded(0)->setQtyReturned(0)
                ->setDiscountRefunded(0)->setBaseDiscountRefunded(0)
                ->setAmountRefunded(0)->setBaseAmountRefunded(0)
                ->setTaxRefunded(0)->setBaseTaxRefunded(0)
                ->setDiscountTaxCompensationRefunded(0)
                ->setBaseDiscountTaxCompensationRefunded(0)
                ->save();
        }

        $state = \Magento\Sales\Model\Order::STATE_PROCESSING;

        $this->getOrder()
            ->setTaxRefunded(0)->setBaseTaxRefunded(0)
            ->setDiscountRefunded(0)->setBaseDiscountRefunded(0)
            ->setSubtotalRefunded(0)->setBaseSubtotalRefunded(0)
            ->setShippingRefunded(0)->setBaseShippingRefunded(0)
            ->setTotalOfflineRefunded(0)->setBaseTotalOfflineRefunded(0)
            ->setTotalRefunded(0)->setBaseTotalRefunded(0)
            ->setState($state)
            ->save();

        $this->getOrder()->getPayment()
            ->setAmountRefunded(0)->setBaseAmountRefunded(0)
            ->setBaseAmountRefundedOnline(0)
            ->setShippingRefunded(0)->setBaseShippingRefunded(0)
            ->save();
    }

    /**
     * @return void
     */
    private function removeAllInvoices()
    {
        $invoices = $this->getOrder()->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            if (!$invoice->isCanceled()) {
                $invoice->cancel()->save()->getOrder()->save();
            }
            $invoice->delete();
        }
        foreach ($this->getOrder()->getAllItems() as $item) {
            $item->setQtyInvoiced(0)->save();
        }

        $this->getOrder()
            ->setTaxInvoiced(0)->setBaseTaxInvoiced(0)
            ->setDiscountInvoiced(0)->setBaseDiscountInvoiced(0)
            ->setSubtotalInvoiced(0)->setBaseSubtotalInvoiced(0)
            ->setTotalInvoiced(0)->setBaseTotalInvoiced(0)
            ->setShippingInvoiced(0)->setBaseShippingInvoiced(0)
            ->setTotalPaid(0)->setBaseTotalPaid(0)
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();
    }

    /**
     * @return void
     */
    private function removeAllShipments()
    {
        $shipments = $this->getOrder()->getShipmentsCollection();
        foreach ($shipments as $shipment) {
            $shipment->delete();
        }

        $items = $this->getOrder()->getItems();
        foreach ($items as $item) {
            $item->setQtyShipped(0)->save();
        }

        $state = \Magento\Sales\Model\Order::STATE_PROCESSING;

        $this->getOrder()->setState($state)->save();

        $this->getOrder()->getPayment()
            ->setShippingCaptured(0)->setBaseShippingCaptured(0)
            ->setShippingRefunded(0)->setBaseShippingRefunded(0)
            ->save();
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createInvoiceForOrder()
    {
        $this->getOrder()
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();

        if ($this->getOrder()->canInvoice()) {
            $order = $this->getOrder();
            $invoice = $this->getOrder()->prepareInvoice();
            if (!$invoice) {
                throw new LocalizedException(__("Can not create invoice"));
            }

            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);

            $invoice->register();

            $transaction = $this->objectManager->create('Magento\Framework\DB\Transaction');
            $transaction->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            /** hack for fix lost $0.01 */
            $order->getPayment()
                ->setBaseAmountPaid($order->getBaseGrandTotal())
                ->setAmountPaid($order->getGrandTotal())
                ->save();
            $order->setBaseTotalPaid($order->getBaseGrandTotal())
                ->setTotalPaid($order->getGrandTotal())
                ->save();
        }
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createShipmentForOrder()
    {
        if ($this->getOrder()->canShip()) {
            $this->shipmentLoader->setOrderId($this->getOrder()->getId());
            $shipment = $this->shipmentLoader->load();
            if (!$shipment) {
                throw new LocalizedException(__("Can not create shipment"));
            }

            $shipment->register();

            $transaction = $this->objectManager->create('Magento\Framework\DB\Transaction');
            $transaction->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();
        }
    }
}
