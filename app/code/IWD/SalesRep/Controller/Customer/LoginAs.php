<?php

namespace IWD\SalesRep\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Class LoginAs
 * @package IWD\SalesRep\Controller\Customer
 */
class LoginAs extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \IWD\SalesRep\Helper\Data
     */
    private $salesrepHelper;

    /**
     * LoginAs constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \IWD\SalesRep\Helper\Data $salesrepHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \IWD\SalesRep\Helper\Data $salesrepHelper
    ) {
        $this->customerSession = $customerSession;
        $this->salesrepHelper = $salesrepHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $current = $this->customerSession->getCustomer();
        $destinationCustomerId = $this->_request->getPost('customer_id');
        if (!$destinationCustomerId) {
            $this->messageManager->addErrorMessage('Customer was not chosen');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        try {
            if ($this->salesrepHelper->isAllowedToLoginAs($this->customerSession->getCustomerId(), $destinationCustomerId)) {
                if ($this->salesrepHelper->loginAsAssignedCustomer($destinationCustomerId)) {
                    $destCustomer = $this->salesrepHelper->getCustomerInfo($destinationCustomerId);
                    $msg = 'You are now placing an order for ' . $destCustomer->getFirstname() . ' ' . $destCustomer->getLastname();
                    $this->messageManager->addSuccessMessage($msg);
                    
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                } else {
                    throw new AuthorizationException(__('Fail to switch to customer account'));
                }
            }
        } catch (AuthorizationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Error during authorization');
        }

        return $resultRedirect;
    }
}
