<?php

namespace IWD\SalesRep\Observer\Common;

use Magento\Framework\Event\ObserverInterface;
use IWD\SalesRep\Model\Customer as AttachedCustomer;
use IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer;

/**
 * Class CheckoutSubmitAllAfterObserver
 * @package IWD\SalesRep\Observer\Common
 */
class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \IWD\SalesRep\Model\OrderFactory
     */
    private $assignedOrderFactory;

    /**
     * @var \IWD\SalesRep\Model\UserFactory
     */
    private $salesUserFactory;
    
    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * CheckoutSubmitAllAfterObserver constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\OrderFactory $assignedOrder
     * @param \IWD\SalesRep\Model\UserFactory $salesUserFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\OrderFactory $assignedOrder,
        \IWD\SalesRep\Model\UserFactory $salesUserFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->assignedOrderFactory = $assignedOrder;
        $this->salesUserFactory = $salesUserFactory;
        $this->userFactory = $userFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $order \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface
         */
        $order = $observer->getOrder();

        $customerId = $order->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        if ($order && $order->getId() && $customer->getData(Customer::KEY_ASSIGNED_SALES_REP)) {
            // check if salesrep active
            $salesRepId = $customer->getData(Customer::KEY_ASSIGNED_SALES_REP);
            
            $salesUser = $this->salesUserFactory->create()->load($salesRepId);
            if ($salesUser && $salesUser->getId()) {
                if ($salesUser->getEnabled() != 1) {
                    return $this;
                }
            } else {
                return $this;
            }

            $status = 0;

            // get Admin User record to check if enabled
            $admin_user_id = $salesUser->getAdminId();
            $adminUser = $this->userFactory->create()->load($admin_user_id);
            if ($adminUser && $adminUser->getId()) {
                $status = $adminUser->getData('is_active');
            }
            
            if ($status != 1) {
                return $this;
            }
            
            try {
                $assignedOrder = $this->assignedOrderFactory->create()
                    ->addData([
                        'order_id' => $order->getId(),
                        'salesrep_id' => $salesRepId,
                        AttachedCustomer::COMMISSION_APPLY_WHEN => $customer->getData(AttachedCustomer::COMMISSION_APPLY_WHEN),
                        AttachedCustomer::COMMISSION_RATE => $customer->getData(AttachedCustomer::COMMISSION_RATE),
                        AttachedCustomer::COMMISSION_TYPE => $customer->getData(AttachedCustomer::COMMISSION_TYPE),
                    ]);
                $assignedOrder->save();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
