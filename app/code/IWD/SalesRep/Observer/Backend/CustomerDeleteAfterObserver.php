<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomerDeleteAfterObserver
 * @package IWD\SalesRep\Observer\Backend
 */
class CustomerDeleteAfterObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //todo: add cleanup of salesrep_customer table
    }
}
