<?php

namespace IWD\SalesRep\Controller\Adminhtml\User;

/**
 * Class Commission
 * @package IWD\SalesRep\Controller\Adminhtml\User
 */
class Commission extends \IWD\SalesRep\Controller\Adminhtml\AbstractUser
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * Commission constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $registry, $customerFactory, $attachedCustomerCollection);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $salesrepId = $this->_request->getParam('salesrep_id');
        $customerId = $this->_request->getParam('customer_id');

        $attachedCustomer = $this->attachedCustomerCollectionFactory->create()
            ->addFieldToFilter('salesrep_id', $salesrepId)
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        $resultLayout = $this->resultLayoutFactory->create();

        if ($attachedCustomer->isEmpty()) {
            $res = [
                'html' => 'Please, assign customer to Sales Rep first',
                'res' => false,
            ];
        } else {
            $this->registry->register('attached_customer', $attachedCustomer);

            $res = [
                'res' => true,
                'html' => $resultLayout->getLayout()
                    ->createBlock('\IWD\SalesRep\Block\Adminhtml\Commission\Edit', 'salesrep_commission')
                    ->toHtml()
            ];
        }

        die(json_encode($res));
    }
}
