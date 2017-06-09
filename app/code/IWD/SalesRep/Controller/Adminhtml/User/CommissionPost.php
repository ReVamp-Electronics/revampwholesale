<?php

namespace IWD\SalesRep\Controller\Adminhtml\User;

use IWD\SalesRep\Model\Customer as AttachedCustomer;

class CommissionPost extends \IWD\SalesRep\Controller\Adminhtml\AbstractUser
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultJsonFactory;

    /**
     * @var \IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer\Commission
     */
    private $commission;

    /**
     * CommissionPost constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer\Commission $commission
    ) {
        parent::__construct($context, $registry, $customerFactory, $attachedCustomerCollection);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->commission = $commission;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $res = [];
        $result = $this->resultJsonFactory->create();
        try {
            $post = $this->_request->getPost();
            $salesrepId = $this->_request->getParam('salesrep_id');
            $customerId = $this->_request->getParam('customer_id');

            $attachedCustomer = $this->attachedCustomerCollectionFactory->create()
                ->addFieldToFilter('salesrep_id', $salesrepId)
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();

            $attachedCustomer->addData([
                AttachedCustomer::COMMISSION_TYPE => $post[AttachedCustomer::COMMISSION_TYPE],
                AttachedCustomer::COMMISSION_RATE => $post[AttachedCustomer::COMMISSION_RATE],
                AttachedCustomer::COMMISSION_APPLY_WHEN => $post[AttachedCustomer::COMMISSION_APPLY_WHEN],
            ]);
            $attachedCustomer->save();
            $customer = $this->customerFactory->create()->load($customerId);
            $res = [
                'res' => true,
                'html' => $this->commission->render($customer),
            ];
        } catch (\Exception $e) {
            $res['res'] = false;
            $result['message'] = $e->getMessage();
        }

        return $result->setData($res);
    }
}
