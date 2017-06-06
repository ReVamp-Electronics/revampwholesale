<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Aheadworks\Helpdesk\Model\Ticket\ExternalKeyEncryptor;
use Aheadworks\Helpdesk\Model\TicketFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Ticket as TicketResource;

/**
 * Class Ticket
 * @package Aheadworks\Helpdesk\Controller
 */
abstract class Ticket extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ExternalKeyEncryptor
     */
    protected $externalKeyEncryptor;

    /**
     * @var TicketFactory
     */
    protected $ticketModelFactory;

    /**
     * @var TicketResource
     */
    protected $ticketResource;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param TicketFactory $ticketModelFactory
     * @param TicketResource $ticketResource
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ExternalKeyEncryptor $externalKeyEncryptor,
        TicketFactory $ticketModelFactory,
        TicketResource $ticketResource
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->externalKeyEncryptor = $externalKeyEncryptor;
        $this->ticketModelFactory = $ticketModelFactory;
        $this->ticketResource = $ticketResource;
    }

    /**
     * Check if customer specified is valid
     *
     * @param int $customerId
     * @param string  $customerEmail
     * @return bool
     */
    protected function isCustomerValid($customerId, $customerEmail)
    {
        $loggedCustomer = $this->customerSession->getCustomer();
        if (
            $loggedCustomer &&
            (
                ($customerId && $customerId == $loggedCustomer->getId()) ||
                $customerEmail == $loggedCustomer->getEmail()
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if external key specified is valid
     *
     * @param string $key
     * @param int $ticketId
     * @return bool
     */
    protected function isExternalKeyValid($key, $ticketId)
    {
        if ($ticketId && $this->externalKeyEncryptor->getTicketId($key) == $ticketId) {
            return true;
        }
        return false;
    }

    /**
     * Get ticket
     *
     * @return \Aheadworks\Helpdesk\Model\Ticket|null
     */
    protected function getTicket()
    {
        /** @var \Aheadworks\Helpdesk\Model\Ticket ticketModel */
        $ticketModel = $this->ticketModelFactory->create();

        $key = $this->getRequest()->getParam('key');
        if ($key) {
            $ticketId = $this->externalKeyEncryptor->getTicketId($key);
            if ($ticketId) {
                $this->ticketResource->load($ticketModel, $ticketId);
                if ($ticketModel->getId()) {
                    return $ticketModel;
                }
            }
        } else {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->ticketResource->load($ticketModel, $id);

                if (
                    $ticketModel->getId()
                    && $this->isCustomerValid($ticketModel->getCustomerId(), $ticketModel->getCustomerEmail())
                ) {
                        return $ticketModel;
                }
            }
        }
        return null;
    }
}
