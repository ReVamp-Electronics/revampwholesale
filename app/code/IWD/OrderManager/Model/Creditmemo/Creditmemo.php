<?php

namespace IWD\OrderManager\Model\Creditmemo;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use IWD\OrderManager\Model\Log\Logger;

/**
 * Class Creditmemo
 * @package IWD\OrderManager\Model\Creditmemo
 */
class Creditmemo extends \Magento\Sales\Model\Order\Creditmemo
{
    /**
     * @var ResourceConnection
     */
    private $appResourceConnection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\Order\Creditmemo\Config $creditmemoConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory $cmItemCollectionFactory
     * @param \Magento\Framework\Math\CalculatorFactory $calculatorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\Creditmemo\CommentFactory $commentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $commentCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ResourceConnection $appResourceConnection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\Creditmemo\Config $creditmemoConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory $cmItemCollectionFactory,
        \Magento\Framework\Math\CalculatorFactory $calculatorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Creditmemo\CommentFactory $commentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $commentCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        ResourceConnection $appResourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $creditmemoConfig,
            $orderFactory,
            $cmItemCollectionFactory,
            $calculatorFactory,
            $storeManager,
            $commentFactory,
            $commentCollectionFactory,
            $priceCurrency,
            $resource,
            $resourceCollection,
            $data
        );

        $this->scopeConfig = $scopeConfig;
        $this->appResourceConnection = $appResourceConnection;
    }

    /**
     * @return void
     */
    public function cancel()
    {
        $this->cancelItems();
        $this->updateOrderStatusAfterCancel();
        $this->cancelOrderTotal();
    }

    /**
     * @return void
     */
    protected function cancelItems()
    {
        $creditmemoItems = $this->getItemsCollection();

        /** @var $creditmemoItem \Magento\Sales\Model\Order\Creditmemo\Item */
        foreach ($creditmemoItems as $creditmemoItem) {
            $orderItems = $this->getOrder()->getItems();

            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() != $creditmemoItem->getProductId()) {
                    continue;
                }

                $amountRefunded = $orderItem->getAmountRefunded() - $creditmemoItem->getRowTotal();
                if ($amountRefunded >= 0) {
                    $orderItem->setAmountRefunded($amountRefunded);
                }

                $baseAmountRefunded = $orderItem->getBaseAmountRefunded() - $creditmemoItem->getRowTotal();
                if ($baseAmountRefunded >= 0) {
                    $orderItem->setBaseAmountRefunded($baseAmountRefunded);
                }

                $taxRefunded = $orderItem->getTaxRefunded() - $creditmemoItem->getTaxAmount();
                if ($taxRefunded >= 0) {
                    $orderItem->setTaxRefunded($taxRefunded);
                }

                $baseTaxRefunded = $orderItem->getBaseTaxRefunded() - $creditmemoItem->getBaseTaxAmount();
                if ($baseTaxRefunded >= 0) {
                    $orderItem->setBaseTaxRefunded($baseTaxRefunded);
                }

                $discountRefunded = $orderItem->getDiscountRefunded() - $creditmemoItem->getDiscountAmount();
                if ($discountRefunded >= 0) {
                    $orderItem->setDiscountRefunded($discountRefunded);
                }

                $baseDiscountRefunded = $orderItem->getBaseDiscountRefunded() - $creditmemoItem->getBaseDiscountAmount();
                if ($baseDiscountRefunded >= 0) {
                    $orderItem->setBaseDiscountRefunded($baseDiscountRefunded);
                }

                $hiddenTaxRefunded = $orderItem->getDiscountTaxCompensationRefunded() - $creditmemoItem->getDiscountTaxCompensationAmount();
                if ($hiddenTaxRefunded >= 0) {
                    $orderItem->setDiscountTaxCompensationRefunded($hiddenTaxRefunded);
                }

                $baseHiddenTaxRefunded = $orderItem->getBaseDiscountTaxCompensationRefunded() - $creditmemoItem->getBaseDiscountTaxCompensationAmount();
                if ($baseHiddenTaxRefunded >= 0) {
                    $orderItem->setBaseDiscountTaxCompensationRefunded($baseHiddenTaxRefunded);
                }

                $qtyRefunded = $orderItem->getQtyRefunded() - $creditmemoItem->getQty();
                if ($qtyRefunded >= 0) {
                    $orderItem->setQtyRefunded($qtyRefunded);
                }

                $orderItem->save();
            }
        }
    }

    /**
     * @return void
     */
    protected function cancelOrderTotal()
    {
        $order = $this->getOrder();
        $totalRefunded = $order->getTotalRefunded() - $this->getBaseGrandTotal();
        $baseTotalRefunded = $order->getTotalRefunded() - $this->getBaseGrandTotal();
        $order->setTotalRefunded($totalRefunded);
        $order->setBaseTotalRefunded($baseTotalRefunded);
        $order->save();
    }

    /**
     * @return void
     */
    protected function updateOrderStatusAfterCancel()
    {
        $order = $this->getOrder();

        if ($order->hasInvoices() && $order->hasShipments()) {
            $state = \Magento\Sales\Model\Order::STATE_COMPLETE;
        } elseif ($order->hasInvoices()) {
            $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
        } else {
            $state = $order->getState();
        }
        $order->setData('state', $state);
        $order->setStatus($order->getConfig()->getStateDefaultStatus($state));
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        $collection = $this->_commentCollectionFactory->create()->setCreditmemoFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }

        $collection = $this->_cmItemCollectionFactory->create()->setCreditmemoFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }

        $this->deleteFromGrid();

        return parent::beforeDelete();
    }

    /**
     * @return void
     */
    protected function deleteFromGrid()
    {
        $id = $this->getId();
        if (!empty($id)) {
            $connection = $this->appResourceConnection
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

            $salesCreditMemosGridTable = $this->appResourceConnection->getTableName('sales_creditmemo_grid');
            $connection->delete($salesCreditMemosGridTable, ['entity_id = (?)' => $id]);
        }
    }

    /**
     * @return void
     */
    public function deleteCreditmemo()
    {
        $order = $this->getOrder();

        $this->cancel();
        $this->delete();

        $this->addLogAboutDeleteCreditmemo($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function addLogAboutDeleteCreditmemo($order)
    {
        $message = __('Credit memo #%1 has been successfully removed.', $this->getIncrementId());
        Logger::getInstance()->addLogIntoLogTable($message, $order->getId(), $order->getIncrementId());
        Logger::getInstance()->addMessage($message);
        Logger::getInstance()->saveLogsAsOrderComments($order);
    }

    /**
     * @return bool
     */
    public function isAllowDeleteCreditmemo()
    {
        return $this->scopeConfig->getValue('iwdordermanager/allow_delete/credit_memos');
    }
}
