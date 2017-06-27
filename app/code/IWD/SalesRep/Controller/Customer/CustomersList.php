<?php

namespace IWD\SalesRep\Controller\Customer;

use Magento\Framework\Registry;
use IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer as CustomerPlugin;

/**
 * Class CustomersList
 * @package IWD\SalesRep\Controller\Customer
 */
class CustomersList extends \Magento\Framework\App\Action\Action
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
     * @var Registry
     */
    private $registry;

    /**
     * CustomersList constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \IWD\SalesRep\Helper\Data $salesrepHelper
     * @param Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \IWD\SalesRep\Helper\Data $salesrepHelper,
        Registry $registry
    ) {
        $this->customerSession = $customerSession;
        $this->salesrepHelper = $salesrepHelper;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $res = [];
        $customer = $this->customerSession->getCustomer();
        if ($customer->getData(CustomerPlugin::KEY_SALESREP_ACCOUNT_ID)) {
            $customersList = $this->salesrepHelper->getSalesrepAssignedCustomers($customer->getData(CustomerPlugin::KEY_SALESREP_ACCOUNT_ID));
            $this->registry->register('customers_list', $customersList);
            $res['html'] = $this->_view->getLayout()->createBlock('\IWD\SalesRep\Block\B2B\CustomersList')->toHtml();
            $res['res'] = true;
        } else {
            $res['res'] = false;
            $res['html'] = 'You don\'t have permission for this action';
        }
        die(json_encode($res));
    }
}
