<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

/**
 * Class NewAction
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses
 */
class NewAction extends AbstractAction
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->getCoreRegistry()->register('iwd_om_source', $this->getSource());

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('IWD_MultiInventory::catalog_warehouses');
        $resultPage->getConfig()->getTitle()->prepend(__('New Source'));

        return $resultPage;
    }
}
