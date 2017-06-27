<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

/**
 * Class MassDelete
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses
 */
class MassDelete extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $source = $this->getSourceRepository()->get($id);
                $this->getSourceRepository()->delete($source);
                $deletedCount++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if ($deletedCount) {
            $this->messageManager->addSuccessMessage(__('A total of %1 source(s) were deleted.', $deletedCount));
        }

        $this->_redirect('*/*/index');
    }
}
