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
 
namespace EH\CustomerApprove\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use EH\CustomerApprove\Helper\Data as CustomerApproveHelper;

/**
 * Class ApproveDisapproveButton
 */
class ApproveDisapproveButton extends GenericButton implements ButtonProviderInterface
{
	protected $customerRepository;
	protected $customerApproveHelper;
	
	public function __construct(
        Context $context,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        CustomerApproveHelper $customerApproveHelper
    ) {
        parent::__construct($context, $registry);
        $this->customerRepository = $customerRepository;
        $this->customerApproveHelper = $customerApproveHelper;
    }
    
    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if($customerId) {
			$customerData = $this->customerRepository->getById($customerId);
			if ($customerId && $this->customerApproveHelper->getIsEnabled()) {
				if($customerData->getCustomAttribute('eh_is_approved')->getValue() == 0){
					$data = [
						'label' => __('Approve'),
						'class' => 'approve',
						'on_click' => sprintf("location.href = '%s';", $this->getApproveUrl()),
						'sort_order' => 40,
					];
				} elseif($customerData->getCustomAttribute('eh_is_approved')->getValue() == 1){
					$data = [
						'label' => __('Disapprove'),
						'class' => 'approve',
						'on_click' => sprintf("location.href = '%s';", $this->getDisapproveUrl()),
						'sort_order' => 40,
					];
				}
			}
		}
        return $data;
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl('customerApprove/index/approve', ['customer_id' => $this->getCustomerId()]);
    }
    
    /**
     * @return string
     */
    public function getDisapproveUrl()
    {
        return $this->getUrl('customerApprove/index/disapprove', ['customer_id' => $this->getCustomerId()]);
    }
}
