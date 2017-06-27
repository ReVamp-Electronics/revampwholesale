<?php

namespace IWD\SalesRep\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesOrderGridCollectionLoadBeforeObserver
 * @package IWD\SalesRep\Observer\Backend
 */
class SalesOrderGridCollectionLoadBeforeObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //todo: add column to order grid collection
    }
}
