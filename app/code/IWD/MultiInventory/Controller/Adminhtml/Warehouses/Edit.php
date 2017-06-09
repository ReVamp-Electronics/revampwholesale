<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

/**
 * Class Edit
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses
 */
class Edit extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', 0);
        if (!$id || $id == 1) {
            $this->messageManager->addErrorMessage(__('Incorrect ID'));
            return $this->_redirect('*/*/');
        }

        $source = $this->getSourceRepository()->get($id);
        if (!$source->getStockId()) {
            $this->messageManager->addErrorMessage(__('Source no longer exists.'));
            return $this->_redirect('*/*/');
        }

        $this->getCoreRegistry()->register('iwd_om_source', $source);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('IWD_MultiInventory::catalog_warehouses');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Source'));

        return $resultPage;
    }
}
