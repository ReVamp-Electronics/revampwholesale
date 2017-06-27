<?php

namespace IWD\SalesRep\Controller\Adminhtml\User;

use IWD\SalesRep\Model\Customer as AttachedCustomer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AttachCustomer
 * @package IWD\SalesRep\Controller\Adminhtml\User
 */
class AttachCustomer extends \IWD\SalesRep\Controller\Adminhtml\AbstractUser
{
    /**
     * @var \IWD\SalesRep\Model\CustomerFactory
     */
    private $attachedCustomerFactory;

    /**
     * @var \IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer\Commission
     */
    private $commission;

    /**
     * AttachCustomer constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection
     * @param \IWD\SalesRep\Model\CustomerFactory $attachedCustomerFactory
     * @param \IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer\Commission $commission
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $attachedCustomerCollection,
        \IWD\SalesRep\Model\CustomerFactory $attachedCustomerFactory,
        \IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer\Commission $commission
    ) {
        parent::__construct($context, $registry, $customerFactory, $attachedCustomerCollection);
        $this->attachedCustomerFactory = $attachedCustomerFactory;
        $this->commission = $commission;
    }

    public function execute()
    {
        try {
            $post = $this->getRequest()->getPost();
            $doAttach = $this->getRequest()->getParam('attach');
            $res = [];

            if ($doAttach) {
                // check if already attached
                $attachedCustomer = $this->attachedCustomerCollectionFactory->create()
                    ->addFieldToFilter(AttachedCustomer::CUSTOMER_ID, $post['customer_id'])
                    ->getFirstItem();

                if (is_object($attachedCustomer) && !$attachedCustomer->isEmpty()) {
                    throw new LocalizedException(__('This customer is already attached to another Sales Rep'));
                }

                $attachedCustomer = $this->attachedCustomerFactory->create();
                $attachedCustomer->addData([
                    AttachedCustomer::SALESREP_ID => $post['salesrep_id'],
                    AttachedCustomer::CUSTOMER_ID => $post['customer_id'],
                ]);
                $attachedCustomer->save();
            } else {
                $attachedCustomer = $this->attachedCustomerCollectionFactory->create()
                    ->addFieldToFilter(AttachedCustomer::SALESREP_ID, $post['salesrep_id'])
                    ->addFieldToFilter(AttachedCustomer::CUSTOMER_ID, $post['customer_id'])
                    ->getFirstItem();

                $attachedCustomer->delete();
            }
            $customerModel = $this->customerFactory->create()->load($attachedCustomer->getCustomerId());
            $res['actionHtml'] = $this->commission->render($customerModel); // action cell html (commission)
            $res['res'] = true;
            die(json_encode($res));
        } catch (\Exception $e) {
            die(json_encode(['res' => false, 'message' => $e->getMessage()]));
        }
    }
}
