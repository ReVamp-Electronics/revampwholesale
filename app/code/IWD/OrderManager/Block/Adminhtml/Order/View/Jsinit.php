<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use IWD\OrderManager\Model\Order\Order;

/**
 * Class Jsinit
 * @package IWD\OrderManager\Block\Adminhtml\Order\View
 */
class Jsinit extends Template
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var string[]
     */
    public $disallowed = [];

    /**
     * Jsinit constructor.
     * @param Context $context
     * @param Order $order
     * @param array $data
     */
    public function __construct(
        Context $context,
        Order $order,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order = $order;
        $this->disallowed = [];

        $this->checkIsEditAllowed();
    }

    /**
     * Check is edit allowed
     *
     * @return void
     */
    protected function checkIsEditAllowed()
    {
        $this->checkOrderStatus();
    }

    /**
     * @return void
     */
    protected function checkOrderStatus()
    {
        $isAllowEditOrder = $this->getOrder()->isAllowEditOrder();

        if (!$isAllowEditOrder) {
            array_unshift(
                $this->disallowed,
                __(
                    "Orders with this status are not permitted to be edited. To modify an order with this status, go to <a href='%1'>Order Manager settings</a> and adjust the order status' that can be modified.",
                    $this->_urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'iwdordermanager'])
                )
            );
        }
    }

    /**
     * @return int
     */
    protected function getOrderId()
    {
        return $this->getRequest()->getParam('order_id', 0);
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->order->load($orderId);
    }

    /**
     * @return string
     */
    public function jsonParamsHistory()
    {
        $data = [
            'urlDelete' => $this->_urlBuilder->getUrl('iwdordermanager/order_history/delete'),
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_history/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_history/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsAddress()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_address/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_address/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsItems()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_items/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsShipping()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_shipping/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_shipping/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsPayment()
    {
        $disallowed = $this->disallowed;
        if ($this->getOrder()->hasInvoices()) {
            array_unshift($disallowed, __('You can not change the payment method for an order with an invoice(s).'));
        }

        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_payment/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_payment/update'),
            'baseUrl' => $this->_urlBuilder->getUrl('sales/order_create/loadBlock'),
            'disallowed' => $disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsCustomer()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_customer/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_customer/update'),
            'urlLoadCustomer' => $this->_urlBuilder->getUrl('iwdordermanager/order_customer/loadCustomer'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function jsonParamsOrderInfo()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/order_info/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_info/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }

    /**
     * @param string $block
     * @return bool
     */
    public function isAllowedAction($block)
    {
        $allow = false;

        if (is_string($block)) {
            $allow = $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_' . $block);
        }

        if (is_array($block)) {
            foreach ($block as $action) {
                $allow |= $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_' . $action);
            }
        }

        return $allow;
    }
}
