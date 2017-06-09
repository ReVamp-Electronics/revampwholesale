<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order;

use IWD\OrderManager\Model\Order\Order;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Order
 */
class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_delete';

    /**
     * @var Order
     */
    private $order;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Order $order
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Order $order
    ) {
        parent::__construct($context);
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $referrerUrl = 'sales/order/index';

        try {
            $this->deleteOrder();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was an error when trying to delete the order. Please try again. ') . $e->getMessage()
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($referrerUrl);
        return $resultRedirect;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function deleteOrder()
    {
        $orderId = $this->getOrderId();

        $order = $this->order->load($orderId);
        $incrementId = $order->getIncrementId();

        if ($order->isAllowDeleteOrder()) {
            $order->delete();
            $this->messageManager->addSuccessMessage(__('You have successfully deleted %1 order(s).', $incrementId));
        } else {
            $this->messageManager->addErrorMessage(
                __('Deletion of orders is not permitted. You may enable this option in the Order Manager settings.')
            );
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getOrderId()
    {
        $orderId = $this->getRequest()->getParam('order_id', null);
        if (empty($orderId)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $orderId;
    }
}
