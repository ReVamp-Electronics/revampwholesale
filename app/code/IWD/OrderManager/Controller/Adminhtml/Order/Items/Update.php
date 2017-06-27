<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use IWD\OrderManager\Controller\Adminhtml\Order\Additional\AbstractAction;

class Update extends AbstractAction
{
    /**
     * @return void
     */
    protected function update()
    {
        $this->updateOrderItems();
        $this->dispatchOrderItemsUpdateEvent();
    }

    /**
     * @return void
     */
    private function updateOrderItems()
    {
        $params = $this->getRequest()->getParams();
        $order = $this->loadOrder();
        $order->editItems($params);
    }

    /**
     * @return void
     */
    private function dispatchOrderItemsUpdateEvent()
    {
        $orderId = $this->getOrderId();
        $params = $this->getRequest()->getParams();
        $this->_eventManager->dispatch(
            'iwd_ordermanager_update_order_items',
            ['order_id' => $orderId, 'params' => $params]
        );
    }

    /**
     * @return string
     */
    protected function prepareResponse()
    {
        if ($this->order->isTotalWasChanged()) {
            return ['result' => 'reload'];
        } else {
            return ['result' => 'reload'];
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_items_edit') ||
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_items_delete') ||
            $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_items_add');
    }
}
