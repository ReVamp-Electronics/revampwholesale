<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Plugin\Quote\Sales\Adminhtml\Order\View;

use Magento\Customer\Api\CustomerRepositoryInterface;

class Info
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->_objectManager     = $objectManager;
    }

    public function beforeToHtml(
        $subject
    ) {
        $order = $this->_getOrder($subject);
        if (!$order) {
            return;
        }
        $customerId = $order->getCustomerId();
        if ($customerId > 0) {
            $customer           = $this->customerRepository->getById($customerId);
            $customerAttributes = $customer->getCustomAttributes();
            if ($customerAttributes) {
                foreach ($customerAttributes as $customerAttribute) {
                    $value = $customerAttribute->getValue();
                    if ($value) {
                        $name = $customerAttribute->getAttributeCode();
                        $name = 'customer_' . $name;
                        $order->setData($name, $value);
                    }
                }
            }
        } else {
            $model = $this->_objectManager
                ->create('Amasty\CustomerAttributes\Model\Customer\GuestAttributes')
                ->loadByOrderId($order->getId());
            if ($model && $model->getId()) {
                foreach ($model->getData() as $key => $value) {
                    if ($key == 'id') {
                        continue;
                    }
                    if ($value) {
                        $name = 'customer_' . $key;
                        $order->setData($name, $value);
                    }
                }
            }
        }

        $subject->setOrder($order);
    }

    protected function _getOrder($subject)
    {
        try {
            $order = $subject->getOrder();
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            return false;
        }

        return $order;
    }
}
