<?php

namespace IWD\SalesRep\Model\Plugin\Customer\Backend;

use \IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class AccountManagement
 * @package IWD\SalesRep\Model\Plugin\Customer\Backend
 */
class AccountManagement
{
    /**
     * @var \IWD\SalesRep\Model\CustomerFactory
     */
    private $assignedCustomerFactory;

    /**
     * AccountManagement constructor.
     * @param \IWD\SalesRep\Model\CustomerFactory $assignedCustomerFactory
     */
    public function __construct(
        \IWD\SalesRep\Model\CustomerFactory $assignedCustomerFactory
    ) {
        $this->assignedCustomerFactory = $assignedCustomerFactory;
    }

    /**
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param \Closure $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param $hash
     * @param string $redirectUrl
     * @return \IWD\SalesRep\Model\Preference\Customer\Data\Customer|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function aroundCreateAccountWithPasswordHash(\Magento\Customer\Model\AccountManagement $subject, \Closure $proceed, \Magento\Customer\Api\Data\CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        /**
         * @var $customer \IWD\SalesRep\Model\Preference\Customer\Data\Customer
         */
        if (method_exists($customer, 'getAssignedSalesrepId') && $customer->getAssignedSalesrepId()) {
            $salesrepAssignedCustomerModel = $this->assignedCustomerFactory->create()
                ->addData([
                    AttachedCustomer::COMMISSION_RATE => $customer->getCommissionRate(),
                    AttachedCustomer::COMMISSION_TYPE => $customer->getCommissionType(),
                    AttachedCustomer::COMMISSION_APPLY_WHEN => $customer->getCommissionApply(),
                    AttachedCustomer::SALESREP_ID => $customer->getAssignedSalesrepId()
                ]);
        }

        $customer = $proceed($customer, $hash, $redirectUrl);

        if (!empty($salesrepAssignedCustomerModel)) {
            $salesrepAssignedCustomerModel
                ->setData(AttachedCustomer::CUSTOMER_ID, $customer->getId())
                ->save();
        }

        return $customer;
    }
}
