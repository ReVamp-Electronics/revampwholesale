<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Sales\Order;

/**
 * Class RequestLink
 * @package Aheadworks\Rma\Block\Sales\Order
 */
class RequestLink extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'sales/order/requestlink.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    protected $orderHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return bool
     */
    public function canReturn()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        if (!$order) {
            return false;
        }
        $itemsAvailable = false;
        foreach ($order->getItems() as $orderItem) {
            if (
                !in_array($orderItem->getProductType(), $this->orderHelper->getNotReturnedOrderItemProductTypes()) &&
                $this->orderHelper->getItemMaxCount($orderItem)
            ) {
                $itemsAvailable = true;
            }
        }
        return ($this->orderHelper->isAllowedForOrder($this->getOrder()) && $itemsAvailable);
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('aw_rma/customer/new', ['id' => $this->getOrder()->getId()]);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
}
