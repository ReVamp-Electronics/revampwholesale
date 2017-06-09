<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses\Product;

use Magento\Framework\Controller\ResultFactory;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;

/**
 * Class Update
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses\Product
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_MultiInventory::iwdmultiinventory_warehouse';

    /**
     * @var array
     */
    private $response = [];

    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * Update constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param MultiStockManagement $multiStockManagement
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        MultiStockManagement $multiStockManagement
    ) {
        parent::__construct($context, $resultPageFactory);

        $this->multiStockManagement = $multiStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->response = [];

        try {
            $params = $this->getRequest()->getParam('stock');
            $this->multiStockManagement->updateStockItems($params);
            $this->response['status'] = true;
        } catch (\Exception $e) {
            $this->response = [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }

        return $this->getJsonResponse();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function getJsonResponse()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($this->response);
    }
}
