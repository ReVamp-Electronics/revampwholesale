<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses\Stocks;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;

/**
 * Class Data
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses\Stocks
 */
class Data extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'IWD_MultiInventory::iwdmultiinventory_warehouse';

    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @var array
     */
    private $response;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \IWD\MultiInventory\Helper\Data $helper
     * @param MultiStockManagement $multiStockManagement
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \IWD\MultiInventory\Helper\Data $helper,
        MultiStockManagement $multiStockManagement
    ) {
        parent::__construct($context);
        $this->multiStockManagement = $multiStockManagement;
        $this->response = [];
    }

    public function execute()
    {
        try {
            $this->prepareResultHtml();
            $this->response['error'] = false;
        } catch (\Exception $e) {
            $this->response['error'] = true;
        }

        return $this->getJsonResponse();
    }

    private function prepareResultHtml()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->multiStockManagement->loadOrder($orderId);

        $this->response['order_items'] = $this->multiStockManagement->getOrderItems();
        $this->response['stocks'] = $this->multiStockManagement->getStocksList();
        $this->response['allowed'] = true;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function getJsonResponse()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($this->response);

        return $resultJson;
    }
}
