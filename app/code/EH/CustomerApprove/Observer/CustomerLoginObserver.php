<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\  Customer Approve/Disapprove 2.0 \\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   EH                            ///////
 \\\\\\\                      * @package    EH_CustomerApprove             \\\\\\\
 ///////    * @author     Extensionhut <info@extensionhut.com>             ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\* @copyright  Copyright 2016 © www.extensionhut.com All right reserved\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

namespace EH\CustomerApprove\Observer;

use Magento\Framework\Event\ObserverInterface;
use EH\CustomerApprove\Helper\Data as CustomerApproveHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Customer Observer Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerLoginObserver implements ObserverInterface
{
    
    protected $customerApproveHelper;
    protected $customerRepository;
    protected $customerSession;
    protected $_storeManager;
    protected $messageManager;
    
    public function __construct(
        CustomerApproveHelper $customerApproveHelper,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager
    ) {
        $this->customerApproveHelper = $customerApproveHelper;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

	
    /**
     * Address after save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
		if($this->customerApproveHelper->getIsEnabled()) {
			$customer = $observer->getCustomer();
			if($this->_customerGroupRestrictions($customer->getGroupId())) {
				$customerData = $this->customerRepository->getById($customer->getId());
				if($customerData->getCustomAttribute('eh_is_approved')->getValue() == 0){
					if ($this->customerApproveHelper->getRedirectEnabled()) {
						$this->customerSession->logout()->setBeforeAuthUrl($this->customerApproveHelper->getRedirectUrl())->setLastCustomerId($customer->getId());
						// redirect customer
						header("Status: 301");
						header('Location: '.$this->customerApproveHelper->getRedirectUrl());
						return;
					} elseif($this->customerApproveHelper->getErrorMsgEnabled()) {
						$this->customerSession->logout()->setBeforeAuthUrl($this->_storeManager->getStore()->getBaseUrl()."customer/account/login")->setLastCustomerId($customer->getId());
						$this->messageManager->addError($this->customerApproveHelper->getErrorMsgText());
						// redirect customer
						header("Status: 301");
						header('Location: '.$this->_storeManager->getStore()->getBaseUrl()."customer/account/login");
						return;
					} else {
						$this->customerSession->logout()->setBeforeAuthUrl($this->_storeManager->getStore()->getBaseUrl()."customer/account/login");
						// redirect customer
						header("Status: 301");
						header('Location: '.$this->_storeManager->getStore()->getBaseUrl()."customer/account/login");
						return;
					}
				}
			}
		}
		return;
    }
	
	private function _customerGroupRestrictions($group_id) {
		$groups = $this->customerApproveHelper->getCustomerGroups();
		if(!$groups) {
			return true;
		} else {
			$group_arr = explode(',',$groups);
			if(in_array($group_id,$group_arr)) {
				return true;
			} else {
				return false;
			}
		}
	}
}
