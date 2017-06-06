<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Aheadworks\Helpdesk\Model\Permission\Validator as PermissionValidator;
use Magento\Backend\App\Action;

/**
 * Class Edit
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class Edit extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * Ticket model factory
     *
     * @var \Aheadworks\Helpdesk\Model\TicketFactory
     */
    private $ticketModelFactory;

    /**
     * Ticket resource model
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket
     */
    private $ticketResourceModel;

    /**
     * @var PermissionValidator
     */
    private $permissionValidator;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Helpdesk\Model\TicketFactory $ticketModelFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Ticket $ticketResource
     * @param PermissionValidator $permissionValidator
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\TicketFactory $ticketModelFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket $ticketResource,
        PermissionValidator $permissionValidator
    ) {
        $this->coreRegistry = $registry;
        $this->ticketModelFactory = $ticketModelFactory;
        $this->ticketResourceModel = $ticketResource;
        $this->permissionValidator = $permissionValidator;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Edit Ticket
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /* @var $ticketModel \Aheadworks\Helpdesk\Model\Ticket */
        $ticketModel = $this->ticketModelFactory->create();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->ticketResourceModel->load($ticketModel, $id);
            if (!$ticketModel->getId()) {
                $this->messageManager->addErrorMessage(__('This ticket no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/ticket/index');
            } elseif (!$this->permissionValidator->viewValidate($ticketModel)) {
                $this->messageManager->addErrorMessage(__('Not enough permissions to view the ticket.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/ticket/index');
            }
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $ticketModel->setData($data);
        }
        $this->coreRegistry->register('aw_helpdesk_ticket', $ticketModel);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Aheadworks_Helpdesk::tickets');
        $resultPage->getConfig()->getTitle()->prepend(
             __('Ticket [%1]', $ticketModel->getUid()) . " " . $ticketModel->getSubject()
        );
        return $resultPage;
    }
}
