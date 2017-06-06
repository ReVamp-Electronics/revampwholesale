<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Ticket;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Aheadworks\Helpdesk\Model\Ticket\ExternalKeyEncryptor;
use Aheadworks\Helpdesk\Model\TicketFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Ticket as TicketResource;
use Magento\Framework\Registry;

/**
 * Class External
 * @package Aheadworks\Helpdesk\Controller\Ticket
 */
class External extends \Aheadworks\Helpdesk\Controller\Ticket
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param TicketFactory $ticketModelFactory
     * @param TicketResource $ticketResource
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ExternalKeyEncryptor $externalKeyEncryptor,
        TicketFactory $ticketModelFactory,
        TicketResource $ticketResource,
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $customerSession, $externalKeyEncryptor, $ticketModelFactory, $ticketResource);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ticketModel = $this->getTicket();

        if (!$ticketModel) {
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('This ticket no longer exists.'));
            return $resultRedirect->setPath('/');
        }

        $key = $this->getRequest()->getParam('key');
        $this->coreRegistry->register('aw_helpdesk_ticket', $ticketModel);
        $this->coreRegistry->register('aw_helpdesk_key', $key);

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(
            "[{$ticketModel->getUid()}] " . $ticketModel->getSubject()
        );
        $this->_view->renderLayout();
    }
}
