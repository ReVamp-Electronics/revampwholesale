<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesrepUserDeleteAfterObserver
 * @package IWD\SalesRep\Observer\Backend
 */
class SalesrepUserDeleteAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $b2bCustomerCollectionFactory;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory
     */
    private $salesrepCustomerCollectionFactory;

    /**
     * SalesrepUserDeleteAfterObserver constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $salesrepCustomerCollectionFactory
    ) {
        $this->salesrepCustomerCollectionFactory = $salesrepCustomerCollectionFactory;
        $this->b2bCustomerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // delete related b2b customers
        $salesrepUser = $observer->getData('data_object');
        $collection = $this->salesrepCustomerCollectionFactory->create()
            ->addFieldToFilter('salesrep_id', $salesrepUser->getId());

        $customerIds = [];
        foreach ($collection as $salesrepCustomer) {
            $customerIds[] = $salesrepCustomer->getCustomerId();
            $salesrepCustomer->delete();
        }

        $customerCollection = $this->b2bCustomerCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $customerIds]);

        foreach ($customerCollection as $b2bCustomer) {
            $b2bCustomer->delete();
        }
    }
}
