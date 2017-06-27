<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Info;

use IWD\OrderManager\Model\Order\OrderData;
use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Model\Log\Logger;
use IWD\OrderManager\Model\Salesrep\Salesrep;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Update extends AbstractAction
{
    /**
     * @var OrderData
     */
    protected $orderData;

    /**
     * @var Salesrep
     */
    protected $salesrep;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \IWD\OrderManager\Helper\Data $helper
     * @param OrderData $orderData
     * @param Salesrep $salesrep
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        OrderData $orderData,
        Salesrep $salesrep
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            AbstractAction::ACTION_UPDATE
        );
        $this->orderData = $orderData;
        $this->salesrep = $salesrep;
    }

    /**
     * @return null|string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $orderInfo = $this->getRequest()->getParam('order_info', []);
        $order = $this->getOrder();
        $order->setData('disable_save_handler', true);
        $order->setParams($orderInfo);

        Logger::getInstance()->addMessageForLevel('order_info', 'Order information was changed');

        $order->updateState()
            ->updateStatus()
            ->updateCreatedAt()
            ->updateStoreId()
            ->updateIncrementId()
            ->save();

        $this->updateSalesrep();

        return $this->prepareResponse();
    }

    protected function updateSalesrep()
    {
        $orderInfo = $this->getRequest()->getParam('order_info', []);
        if (isset($orderInfo['salesrep_id'])) {
            $this->salesrep->updateSalesrep(
                $this->getOrderId(),
                $orderInfo['salesrep_id'],
                $this->getOrder()->getCustomerId()
            );
        }
    }

    /**
     * @return string
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return OrderData
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->orderData->load($orderId);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_info');
    }
}
