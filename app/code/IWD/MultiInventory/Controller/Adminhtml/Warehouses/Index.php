<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

/**
 * Class Index
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses
 */
class Index extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Sources'));
        return $resultPage;
    }
}
