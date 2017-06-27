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

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use EH\CustomerApprove\Helper\Data as CustomerApproveHelper;
    
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDisapprove extends \Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    protected $customerApproveHelper;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerApproveHelper $customerApproveHelper
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
        $this->customerApproveHelper = $customerApproveHelper;
    }
    
    protected function massAction(AbstractCollection $collection)
    {
		if(!$this->customerApproveHelper->getIsEnabled()) {
			$this->messageManager->addError(__('Customer approve/disapprove extension is disabled!'));
			$this->_redirect('customer/index/index');
			return;
		}
		// count of updated records
		$updatedCount = 0;
		try {
			foreach ($collection->getAllIds() as $customerId) {				
				$customerData = $this->customerRepository->getById($customerId);					
				// approve customer
				$isApproved = $customerData->setCustomAttribute('eh_is_approved', 0);
				$this->customerRepository->save($customerData);
				$updatedCount++;
			}
			if($updatedCount) {
				$this->messageManager->addSuccess(__('A total of %1 customer(s) were disapproved.', $updatedCount));
			}
		} catch (\Exception $e){
			$this->messageManager->addError($e->getMessage());
		}
		
        $this->_redirect('customer/index/index');
		return;
    }
}
