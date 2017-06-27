<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;

/**
 * Class Coupon
 * @package IWD\OrderManager\Block\Adminhtml\Order\View
 */
class Coupon extends Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Coupon constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue('iwdordermanager/general/enable_edit_coupon');
    }

    /**
     * @return string
     */
    public function getCouponCode()
    {
        return $this->getOrder()->getCouponCode();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @return string
     */
    public function jsonJsParams()
    {
        $data = [
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/order_coupon/update')
        ];

        return json_encode($data);
    }
}
