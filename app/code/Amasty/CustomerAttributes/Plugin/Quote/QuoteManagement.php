<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Plugin\Quote;

use Magento\Quote\Api\Data\PaymentInterface;
use Amasty\CustomerAttributes\Helper\Session;

class QuoteManagement
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $sessionHelper;

    /**
     * @var \Amasty\CustomerAttributes\Model\Customer\GuestAttributesFactory
     */
    private $attributeFactory;

    public function __construct(
        \Amasty\CustomerAttributes\Model\Customer\GuestAttributesFactory $attributeFactory,
        Session $sessionHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->sessionHelper = $sessionHelper;
        $this->registry      = $registry;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param QuoteManagement  $quote
     * @param \Closure         $proceed
     * @param string           $cartId
     * @param PaymentInterface $paymentMethods
     */
    public function aroundPlaceOrder(
        $quote,
        \Closure $proceed,
        $cartId,
        $paymentMethods = null
    ) {
        $customAttributes = $this->sessionHelper->getCustomerAttributesFromSession();
        $orderId = $proceed($cartId, $paymentMethods);

        if ($customAttributes) {
            $this->_saveCustomerAttributesGuest($orderId, $customAttributes);
        }

        return $orderId;
    }

    protected function _saveCustomerAttributesGuest($orderId, $customAttributes)
    {
        /** var $model \Amasty\CustomerAttributes\Model\Customer\GuestAttributes */
        $model = $this->attributeFactory->create();
        $model->setData($customAttributes);
        $model->setOrderId($orderId);
        $model->save();
    }
}
