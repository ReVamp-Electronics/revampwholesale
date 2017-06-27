<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\View;

use IWD\OrderManager\Model\Order\Order;
use Magento\Backend\Block\Widget\Container;

/**
 * Class Toolbar
 * @package IWD\OrderManager\Block\Adminhtml\Order\View
 */
class Toolbar extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var Order
     */
    private $order;

    /**
     * Toolbar constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Order $order
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Order $order,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->order = $order;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addPrintButton();

        if ($this->isAllowDeleteOrder()) {
            $this->addDeleteButton();
        }
    }

    /**
     * @return void
     */
    private function addPrintButton()
    {
        $this->addButton(
            'iwd_print_order',
            [
                'label'   => 'Print',
                'class'   => 'print',
                'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
            ]
        );
    }

    /**
     * @return void
     */
    private function addDeleteButton()
    {
        $message = __('Are you sure you want to DELETE an order?');
        $url = $this->getDeleteUrl();
        $this->addButton(
            'iwd_order_delete',
            [
                'label'   => 'Delete',
                'class'   => 'delete',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')",
            ]
        );
    }

    /**
     * @return bool
     */
    protected function isAllowDeleteOrder()
    {
        $orderId = $this->getOrderId();
        $order = $this->order->load($orderId);

        return $order->isAllowDeleteOrder();
    }

    /**
     * @return string
     */
    protected function getDeleteUrl()
    {
        return $this->getUrl('iwdordermanager/order/delete', ['order_id' => $this->getOrderId()]);
    }

    /**
     * @return string
     */
    protected function getPrintUrl()
    {
        return $this->getUrl('iwdordermanager/order/print', ['order_id' => $this->getOrderId()]);
    }

    /**
     * @return integer
     */
    protected function getOrderId()
    {
        return $this->coreRegistry->registry('current_order')->getId();
    }
}
