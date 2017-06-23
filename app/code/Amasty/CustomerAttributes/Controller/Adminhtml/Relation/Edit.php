<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Controller\Adminhtml\Relation;

use Amasty\CustomerAttributes\Controller\RegistryConstants;

class Edit extends \Amasty\CustomerAttributes\Controller\Adminhtml\Relation
{
    public function execute()
    {
        $relationId = $this->getRequest()->getParam('relation_id');
        if ($relationId) {
            try {
                $model = $this->relationRepository->get($relationId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Relation does not exist.'));
                $this->_redirect('amcustomerattr/*');
                return;
            }
        } else {
            /** @var \Amasty\CustomerAttributes\Model\Relation $model */
            $model = $this->relationFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->coreRegistry->register(RegistryConstants::CURRENT_RELATION_ID, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Customer Attribute Relation'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getName() ? __("Edit Relation \"%1s\"", $model->getName()) : __('New Customer Attribute Relation')
        );

        $breadcrumb = $relationId ? __('Edit Customer Attribute Relation') : __('New Customer Attribute Relation');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
