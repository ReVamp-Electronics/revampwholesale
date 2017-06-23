<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Controller\Adminhtml\Relation;

class Delete extends \Amasty\CustomerAttributes\Controller\Adminhtml\Relation
{
    public function execute()
    {
        $relationId = $this->getRequest()->getParam('relation_id');
        if ($relationId) {
            try {
                $this->relationRepository->deleteById($relationId);
                $this->messageManager->addSuccessMessage(__('The Relation has been deleted.'));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Relation does not exist.'));
            }
        }

        $this->_redirect('amcustomerattr/*');
    }
}
