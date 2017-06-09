<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Massactions;

use IWD\OrderManager\Model\Order\Order;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Editstatus
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Massactions
 */
class Editstatus extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_change_status';

    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Order $order
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Order $order
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countUpdatedOrders = 0;
        $status = $this->getRequest()->getParam('status', null);
        if (empty($status)) {
            $this->messageManager->addErrorMessage(
                __('The status of these orders cannot be changed. You may enable this option in the Order Manager settings.')
            );
        } else {
            foreach ($collection->getItems() as $order) {
                $order = $this->order->load($order->getId());
                $order->updateOrderStatus($status);
                $countUpdatedOrders++;
            }
            $countNonCancelOrder = count($collection->getItems()) - $countUpdatedOrders;

            if ($countNonCancelOrder && $countUpdatedOrders) {
                $this->messageManager->addErrorMessage(
                    __('Status has not been updated for %1 order(s).', $countNonCancelOrder)
                );
            } elseif ($countNonCancelOrder) {
                $this->messageManager->addErrorMessage(
                    __('The status of these orders cannot be changed. You may enable this option in the Order Manager settings.')
                );
            }

            if ($countUpdatedOrders) {
                $this->messageManager->addSuccessMessage(
                    __('The status for orders %1 has been updated.', $countUpdatedOrders)
                );
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * @return string
     */
    protected function getComponentRefererUrl()
    {
        return 'sales/order/index';
    }
}
