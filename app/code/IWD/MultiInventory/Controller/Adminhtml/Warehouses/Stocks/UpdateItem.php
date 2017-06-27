<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses\Stocks;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use IWD\MultiInventory\Controller\Adminhtml\Warehouses\AbstractAction;
use IWD\MultiInventory\Model\Warehouses\Source;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use IWD\MultiInventory\Model\Warehouses\StockOrderItem;
use IWD\MultiInventory\Api\SourceRepositoryInterface;
use IWD\MultiInventory\Api\Data\SourceInterface;
use IWD\MultiInventory\Api\SourceAddressRepositoryInterface;
use IWD\MultiInventory\Api\Data\SourceAddressInterface;

/**
 * Class UpdateItem
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses\Stocks
 */
class UpdateItem extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_MultiInventory::iwdmultiinventory_warehouse';

    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @var StockOrderItem
     */
    private $stockOrderItem;

    /**
     * UpdateItem constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Source $source
     * @param SourceRepositoryInterface $sourceRepositoryInterface
     * @param SourceInterface $sourceInterface
     * @param SourceAddressRepositoryInterface $sourceAddressRepositoryInterface
     * @param SourceAddressInterface $sourceAddressInterface
     * @param MultiStockManagement $multiStockManagement
     * @param StockOrderItem $stockOrderItem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \IWD\MultiInventory\Model\Warehouses\Source $source,
        SourceRepositoryInterface $sourceRepositoryInterface,
        SourceInterface $sourceInterface,
        SourceAddressRepositoryInterface $sourceAddressRepositoryInterface,
        SourceAddressInterface $sourceAddressInterface,
        MultiStockManagement $multiStockManagement,
        StockOrderItem $stockOrderItem
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $source,
            $sourceRepositoryInterface,
            $sourceInterface,
            $sourceAddressRepositoryInterface,
            $sourceAddressInterface
        );
        $this->multiStockManagement = $multiStockManagement;
        $this->stockOrderItem = $stockOrderItem;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $result = ['status' => 1];

        try {
            $stockItems = $this->getRequest()->getParam('stock', []);
            $orderItems = $this->getRequest()->getParam('order', []);
            $orderId = $this->getRequest()->getParam('order_id', 0);

            $this->multiStockManagement->loadOrder($orderId);
            $this->multiStockManagement->updateStockItems($stockItems);
            $this->multiStockManagement->updateOrderItems($orderItems);
            $this->multiStockManagement->updateOrderAssignedQty();

            $result['stockOrderItems'] = $this->stockOrderItem->getStockOrderItems($orderId);
            $result['id'] = $orderId;
        } catch (\Exception $e) {
            $result = ['status' => 0, 'message' => $e->getMessage()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}
