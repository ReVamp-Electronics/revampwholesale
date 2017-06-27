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
 
namespace EH\CustomerApprove\Controller\Adminhtml\Index;

    
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Disapprove extends \EH\CustomerApprove\Controller\Adminhtml\Index
{
    
    public function execute()
    {
		// get customer id
    	$id = $this->getRequest()->getParam('customer_id');
    	if($id) {
			try {
				$customerData = $this->customerRepository->getById($id);
				if (!$customerData->getId()) {
					$this->messageManager->addError(__('This customer no longer exist or invalid customer id.'));
				} else if ($customerData->getCustomAttribute('eh_is_approved')->getValue() == 0) {
					$this->messageManager->addError(__('This customer is already unapproved.'));
				} else {
					// disapprove customer
					$isApproved = $customerData->setCustomAttribute('eh_is_approved', 0);
					$this->customerRepository->save($customerData);
					// add success message
					$this->messageManager->addSuccess(__('The customer has been disapproved.'));
				}
			} catch (\Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
		}
        $this->_redirect('customer/index/edit', array('id' => $id));
		return;
    }
}
