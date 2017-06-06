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
 * Class View
 * @package Aheadworks\Helpdesk\Controller\Ticket
 */
class View extends \Aheadworks\Helpdesk\Controller\Ticket
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;


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
            $this->messageManager->addErrorMessage(__('This ticket no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('aw_helpdesk/ticket');
        }

        $this->coreRegistry->register('aw_helpdesk_ticket', $ticketModel);

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(
            "[{$ticketModel->getUid()}] " . $ticketModel->getSubject()
        );
        /** @var \Magento\Customer\Block\Account\Dashboard $linkBack */
        $linkBack = $this->_view->getLayout()->getBlock('customer.account.link.back');
        if ($linkBack) {
            $linkBack->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $this->_view->renderLayout();
    }

    /**
     * Back redirect
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Check customer authentication
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
