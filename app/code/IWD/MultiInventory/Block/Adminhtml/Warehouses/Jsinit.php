<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use IWD\MultiInventory\Model\Warehouses\StockOrderItem;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;

/**
 * Class Jsinit
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses
 */
class Jsinit extends Template
{
    /**
     * @var string[]
     */
    private $disallowed = [];

    /**
     * @var StockOrderItem
     */
    private $stockOrderItem;

    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Jsinit constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param StockOrderItem $stockOrderItem
     * @param MultiStockManagement $multiStockManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        StockOrderItem $stockOrderItem,
        MultiStockManagement $multiStockManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->stockOrderItem = $stockOrderItem;
        $this->disallowed = [];
        $this->multiStockManagement = $multiStockManagement;
    }

    /**
     * @return string
     */
    public function jsonParamsWarehouses()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdmultiinventory/warehouses/stocks_data'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdmultiinventory/warehouses/stocks_update')
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsOrderViewWarehouse()
    {
        $orderId = $this->getOrderId();
        $this->multiStockManagement->setOrder($this->getOrder());

        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdmultiinventory/warehouses/stocks_data'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdmultiinventory/warehouses/stocks_updateitem'),
            'orderId' => $this->getOrderId(),
            'isNotApplicable' => $this->multiStockManagement->getIsOrderStockNotApplicable(),
            'stockOrderItems' => $this->stockOrderItem->getStockOrderItems($orderId)
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsCreditmemoInfo()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdmultiinventory/creditmemo_info/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdmultiinventory/creditmemo_info/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return int
     */
    private function getOrderId()
    {
        return $this->getRequest()->getParam('order_id', 0);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->orderRepository->get($orderId);
    }
}
