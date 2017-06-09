<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use IWD\SalesRep\Model\User as SalesrepUser;

class AdminUserDeleteAfterObserver implements ObserverInterface
{
    /**
     * @var \IWD\SalesRep\Model\UserFactory
     */
    private $salesrepUserFactory;

    /**
     * AdminUserDeleteAfterObserver constructor.
     * @param \IWD\SalesRep\Model\UserFactory $salesrepUserFactory
     */
    public function __construct(\IWD\SalesRep\Model\UserFactory $salesrepUserFactory)
    {
        $this->salesrepUserFactory = $salesrepUserFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $adminUser = $observer->getData('data_object');
        $salesrepUser = $this->salesrepUserFactory->create()->load($adminUser->getId(), SalesrepUser::ADMIN_ID);
        $salesrepUser->delete();
    }
}
