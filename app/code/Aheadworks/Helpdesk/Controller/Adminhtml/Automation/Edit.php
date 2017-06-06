<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Automation;

use Magento\Backend\App\Action;

/**
 * Class Edit
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Automation
 */
class Edit extends \Aheadworks\Helpdesk\Controller\Adminhtml\Automation
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Ticket model factory
     *
     * @var \Aheadworks\Helpdesk\Model\AutomationFactory
     */
    protected $automationModelFactory;

    /**
     * Ticket resource model
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation
     */
    protected $automationResourceModel;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Helpdesk\Model\AutomationFactory $automationModelFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationModelFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
    ) {
        $this->coreRegistry = $registry;
        $this->automationModelFactory = $automationModelFactory;
        $this->automationResourceModel = $automationResource;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Edit Ticket
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /* @var $automationModel \Aheadworks\Helpdesk\Model\Automation */
        $automationModel = $this->automationModelFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->automationResourceModel->load($automationModel, $id);
            if (!$automationModel->getId()) {
                $this->messageManager->addErrorMessage(__('This automation no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/*');
            }
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $automationModel->setData($data);
        }
        $this->coreRegistry->register('aw_helpdesk_automation', $automationModel);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Aheadworks_Helpdesk::automation');
        $resultPage->getConfig()->getTitle()->prepend(
            $automationModel->getId() ? __('Edit Automation [%1]', $automationModel->getName()) : __('Create New Automation')
        );
        return $resultPage;
    }
}
