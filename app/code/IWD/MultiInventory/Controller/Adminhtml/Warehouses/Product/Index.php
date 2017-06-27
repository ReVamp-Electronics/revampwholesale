<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses\Product;

/**
 * Class Index
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses\Product
 */
class Index extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_MultiInventory::iwdmultiinventory_warehouse';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Stocks for Products'));
        return $resultPage;
    }
}
