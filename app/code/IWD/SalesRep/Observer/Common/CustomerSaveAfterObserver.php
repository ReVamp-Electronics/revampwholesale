<?php

namespace IWD\SalesRep\Observer\Common;

use Magento\Framework\Event\ObserverInterface;
use IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class CustomerSaveAfterObserver
 * @package IWD\SalesRep\Observer\Common
 */
class CustomerSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \IWD\SalesRep\Model\UserFactory
     */
    private $salesrepUserFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory
     */
    private $salesrepCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * CustomerSaveAfterObserver constructor.
     * @param \IWD\SalesRep\Model\UserFactory $salesrepUserFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \IWD\SalesRep\Model\UserFactory $salesrepUserFactory,
        \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory
    ) {
        $this->salesrepUserFactory = $salesrepUserFactory;
        $this->userFactory = $userFactory;
        $this->salesrepCustomerCollectionFactory = $salesrepCustomerCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerDataObject = $observer->getData('customer_data_object');
        $customerId = $customerDataObject->getId();

        $data = [
            'firstname' => $customerDataObject->getFirstname(),
            'lastname' => $customerDataObject->getLastname(),
            'email' => $customerDataObject->getEmail()
        ];

        $salesrepCustomer = $this->salesrepCustomerCollectionFactory
            ->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        if (!($salesrepCustomer && !$salesrepCustomer->isEmpty())) {
            return;
        }

        $salesrepId = $salesrepCustomer->getData('salesrep_id');

        // save admin user
        $salesrepUser = $this->salesrepUserFactory->create()->load($salesrepId, \IWD\SalesRep\Model\User::SALESREP_ID);
        $adminModel = $this->userFactory->create()->load($salesrepUser->getAdminId());
        $adminModel->addData($data);
        $adminModel->save();
        // END save admin user

        // save b2b customers in other stores
        $salesrepCustomersCollection = $this->salesrepCustomerCollectionFactory
            ->create()
            ->addFieldToFilter('salesrep_id', $salesrepId);
        $customerIds = [];
        foreach ($salesrepCustomersCollection->getItems() as $salesrepCustomer) {
            if ($salesrepCustomer->getData('customer_id') != $customerId) {
                $customerIds[] = $salesrepCustomer->getData('customer_id');
            }
        }

        $otherCustomersCollection = $this->customerCollectionFactory
            ->create()
            ->addFieldToFilter('entity_id', ['in' => $customerIds]);

        foreach ($otherCustomersCollection->getItems() as $customer) {
            $customer->addData($data);
            $customer->save();
        }
        // END save b2b customers in other stores
    }
}
