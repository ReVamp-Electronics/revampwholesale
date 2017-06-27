<?php

namespace IWD\OrderManager\Model\Invoice;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\AttributeValueFactory;
use IWD\OrderManager\Model\Log\Logger;

class Invoice extends \Magento\Sales\Model\Order\Invoice
{
    /**
     * @var ResourceConnection
     */
    protected $appResourceConnection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \IWD\OrderManager\Model\Creditmemo\Creditmemo
     */
    protected $creditmemo;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\Order\Invoice\Config $invoiceConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Math\CalculatorFactory $calculatorFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory
     * @param \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $commentCollectionFactory
     * @param ResourceConnection $appResourceConnection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \IWD\OrderManager\Model\Creditmemo\Creditmemo $creditmemo
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\Invoice\Config $invoiceConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Math\CalculatorFactory $calculatorFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory,
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $commentCollectionFactory,
        ResourceConnection $appResourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \IWD\OrderManager\Model\Creditmemo\Creditmemo $creditmemo,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $invoiceConfig,
            $orderFactory,
            $calculatorFactory,
            $invoiceItemCollectionFactory,
            $invoiceCommentFactory,
            $commentCollectionFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_scopeConfig = $scopeConfig;
        $this->creditmemo = $creditmemo;
        $this->appResourceConnection = $appResourceConnection;
    }

    /**
     * @return $this
     */
    public function beforeDelete()
    {
        $this->deleteComments();
        $this->deleteInvoiceItems();
        $this->deleteFromGrid();

        return parent::beforeDelete();
    }

    /**
     * @return void
     */
    protected function deleteComments()
    {
        $collection = $this->_commentCollectionFactory->create()
            ->setInvoiceFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }
    }

    /**
     * @return void
     */
    protected function deleteInvoiceItems()
    {
        $collection = $this->_invoiceItemCollectionFactory->create()
            ->setInvoiceFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }
    }

    /**
     * @return void
     */
    protected function deleteFromGrid()
    {
        $id = $this->getId();
        if (!empty($id)) {
            $connection = $this->appResourceConnection
                ->getConnection(ResourceConnection::DEFAULT_CONNECTION);

            $salesInvoiceGridTable = $this->appResourceConnection->getTableName('sales_invoice_grid');
            $connection->delete($salesInvoiceGridTable, ['entity_id = (?)' => $id]);
        }
    }

    /**
     * @return bool
     */
    public function isAllowDeleteInvoice()
    {
        return $this->_scopeConfig->getValue('iwdordermanager/allow_delete/invoices');
    }

    /**
     * @return void
     */
    public function deleteInvoice()
    {
        $order = $this->getOrder();
        $this->deleteRelatedCreditMemos();
        $this->cancelInvoice();
        $this->delete();

        $this->addLogAboutDeleteInvoice($order);
    }

    /**
     * @return bool
     */
    protected function cancelInvoice()
    {
        try {
            if (!$this->isCanceled()) {
                $this->cancel()->save()->getOrder()->save();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    protected function deleteRelatedCreditMemos()
    {
        $creditMemos = $this->getOrder()->getCreditmemosCollection();
        foreach ($creditMemos as $creditMemo) {
            $creditMemo = $this->creditmemo->load($creditMemo->getEntityId());
            $creditMemo->cancel();
            $creditMemo->delete();
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function addLogAboutDeleteInvoice($order)
    {
        $message = __('Invoice #%1 has been successfully removed.', $this->getIncrementId());
        $logger = Logger::getInstance();

        $logger->addLogIntoLogTable($message, $order->getId(), $order->getIncrementId());
        $logger->addMessage($message);
        $logger->saveLogsAsOrderComments($order);
    }
}
