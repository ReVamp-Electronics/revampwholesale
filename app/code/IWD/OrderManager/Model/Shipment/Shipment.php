<?php

namespace IWD\OrderManager\Model\Shipment;

use Magento\Framework\App\ResourceConnection;
use IWD\OrderManager\Model\Log\Logger;

/**
 * Class Shipment
 * @package IWD\OrderManager\Model\Shipment
 */
class Shipment extends \Magento\Sales\Model\Order\Shipment
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param \Magento\Sales\Model\Order\Shipment\CommentFactory $commentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\Order\Shipment\CommentFactory $commentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ResourceConnection $resourceConnection,
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
            $shipmentItemCollectionFactory,
            $trackCollectionFactory,
            $commentFactory,
            $commentCollectionFactory,
            $orderRepository,
            $resource,
            $resourceCollection,
            $data
        );

        $this->orderRepo = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        $collection = $this->_commentCollectionFactory->create()->setShipmentFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }

        $collection = $this->_shipmentItemCollectionFactory->create()->setShipmentFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }

        $collection = $this->_trackCollectionFactory->create()->setShipmentFilter($this->getId());
        foreach ($collection as $object) {
            $object->delete();
        }

        $this->deleteFromGrid();

        return parent::beforeDelete();
    }

    /**
     * Delete From Grid
     *
     * @return void
     */
    private function deleteFromGrid()
    {
        $id = $this->getId();
        if (!empty($id)) {
            $connection = $this->resourceConnection->getConnection(
                \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            );
            $gridTable = $this->resourceConnection->getTableName('sales_shipment_grid');
            $connection->delete($gridTable, ['entity_id = (?)' => $id]);
        }
    }

    /**
     * @return bool
     */
    public function isAllowDeleteShipment()
    {
        return $this->scopeConfig->getValue('iwdordermanager/allow_delete/shipments');
    }

    /**
     * @return void
     */
    public function deleteShipment()
    {
        $order = $this->getOrder();

        $this->cancel();
        $this->delete();

        $this->addLogAboutDeleteShipment($order);
    }

    /**
     * @return void
     */
    public function cancel()
    {
        $this->cancelItems();
        $this->changeOrderStatusAfterDeleteShipment();
    }

    /**
     * @return void
     */
    private function cancelItems()
    {
        $shipmentItems = $this->getItemsCollection();

        /**
         * @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
         */
        foreach ($shipmentItems as $shipmentItem) {
            $orderItems = $this->getOrder()->getItems();
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() != $shipmentItem->getProductId()) {
                    continue;
                }

                $qty = $orderItem->getQtyShipped() - $shipmentItem->getQty();
                $orderItem->setQtyShipped($qty)->save();
            }
        }
    }

    /**
     * @return void
     */
    private function changeOrderStatusAfterDeleteShipment()
    {
        $order = $this->getOrder();

        $state = ($order->hasInvoices())
            ? \Magento\Sales\Model\Order::STATE_PROCESSING
            : \Magento\Sales\Model\Order::STATE_NEW;

        $order->setData('state', $state);
        $order->setStatus($order->getConfig()->getStateDefaultStatus($state));

        $this->orderRepo->save($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function addLogAboutDeleteShipment($order)
    {
        $message = __('Shipment #%1 has been removed', $this->getIncrementId());
        Logger::getInstance()->addLogIntoLogTable($message, $order->getId(), $order->getIncrementId());
        Logger::getInstance()->addMessage($message);
        Logger::getInstance()->saveLogsAsOrderComments($order);
    }
}
