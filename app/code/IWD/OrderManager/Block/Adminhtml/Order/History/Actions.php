<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\History;

use \Magento\Backend\Block\Template;

/**
 * Class Actions
 * @package IWD\OrderManager\Block\Adminhtml\Order\History
 */
class Actions extends Template
{
    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * @var \IWD\OrderManager\Model\Order\Item
     */
    private $item;

    /**
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Item $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param string $block
     * @return bool
     */
    public function isAllowedAction($block)
    {
        return $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_' . $block);
    }

    /**
     * @return bool
     */
    public function isAllowedOrderStatus()
    {
        $orderStatus = $this->getOrder()->getStatus();
        $allowedStatuses = $this->_scopeConfig->getValue('iwdordermanager/general/order_statuses');
        $allowedStatuses = explode(',', $allowedStatuses);

        return in_array($orderStatus, $allowedStatuses);
    }
}
