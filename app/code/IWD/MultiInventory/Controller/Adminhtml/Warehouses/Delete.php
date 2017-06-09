<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

/**
 * Class Delete
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses
 */
class Delete extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id', 0);
            if (!$id || $id == 1) {
                $this->messageManager->addErrorMessage(__('Incorrect ID'));
                return $this->_redirect('*/*/');
            }

            $sourceModel = $this->getSourceRepository()->get($id);
            if (!$sourceModel->getStockId()) {
                $this->messageManager->addErrorMessage(__('Source no longer exists.'));
                return $this->_redirect('*/*/');
            }

            $this->getSourceRepository()->delete($sourceModel);
            $this->messageManager->addSuccessMessage(__('The source has been deleted.'));
            return $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/edit', ['id' => $sourceModel->getId()]);
        }
    }
}
