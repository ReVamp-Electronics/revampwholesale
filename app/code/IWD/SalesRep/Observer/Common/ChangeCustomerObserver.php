<?php

namespace IWD\SalesRep\Observer\Common;

use Magento\Framework\Event\ObserverInterface;
use IWD\SalesRep\Model\Customer;

/**
 * Class ChangeCustomerObserver
 * @package IWD\SalesRep\Observer\Common
 */
class ChangeCustomerObserver implements ObserverInterface
{
    /**
     * @var \IWD\SalesRep\Model\OrderFactory
     */
    private $assignedOrder;

    /**
     * @var \IWD\SalesRep\Model\CustomerFactory
     */
    private $assignedCustomer;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * ChangeCustomerObserver constructor.
     * @param \IWD\SalesRep\Model\OrderFactory $assignedOrder
     * @param \IWD\SalesRep\Model\CustomerFactory $assignedCustomer
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \IWD\SalesRep\Model\OrderFactory $assignedOrder,
        \IWD\SalesRep\Model\CustomerFactory $assignedCustomer,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->assignedOrder = $assignedOrder;
        $this->assignedCustomer = $assignedCustomer;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getData('order_id');
        $customerId = $observer->getData('customer_id');

        $salesrepOrder = $this->getAssignedSalesrepOrder($orderId);

        $this->updateSalesrepForOrder($salesrepOrder, $customerId, $orderId);
    }

    /**
     * @param $orderId
     * @return \Magento\Framework\DataObject
     */
    private function getAssignedSalesrepOrder($orderId)
    {
        return $this->assignedOrder
            ->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setPageSize(1)
            ->getFirstItem();
    }

    /**
     * @param $salesrepOrder
     * @param $customerId
     * @param $orderId
     */
    private function updateSalesrepForOrder($salesrepOrder, $customerId, $orderId)
    {
        $salesrepCustomer = $this->getAssignedSalesrepCustomer($customerId);

        if (!$salesrepCustomer || $salesrepCustomer->isEmpty()) {
            $salesrepOrder->delete();
        } else {
            $salesrepId = $salesrepCustomer->getSalesrepId();
            $customer = $this->customerFactory->create()->load($customerId);
            $salesrepOrder
                ->setSalesrepId($salesrepId)
                ->setOrderId($orderId)
                ->setData(Customer::COMMISSION_APPLY_WHEN, $customer->getData(Customer::COMMISSION_APPLY_WHEN))
                ->setData(Customer::COMMISSION_RATE, $customer->getData(Customer::COMMISSION_RATE))
                ->setData(Customer::COMMISSION_TYPE, $customer->getData(Customer::COMMISSION_TYPE))
                ->save();
        }
    }

    /**
     * @param $customerId
     * @return mixed
     */
    private function getAssignedSalesrepCustomer($customerId)
    {
        return $this->assignedCustomer
            ->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setPageSize(1)
            ->getFirstItem();
    }
}
