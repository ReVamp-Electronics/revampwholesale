<?php

namespace IWD\SalesRep\Model\Plugin\Customer\ResourceModel;

use IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class Customer
 * @package IWD\SalesRep\Model\Plugin\Customer\ResourceModel
 */
class Customer
{
    const KEY_ASSIGNED_SALES_REP = 'assigned_salesrep_id';
    const KEY_SALESREP_ACCOUNT_ID = 'salesrep_user_id';

    /**
     * @var \IWD\SalesRep\Helper\Data
     */
    private $salesrepHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * Customer constructor.
     * @param \IWD\SalesRep\Helper\Data $salesrepHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    function __construct(
        \IWD\SalesRep\Helper\Data $salesrepHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->salesrepHelper = $salesrepHelper;
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer $subject
     * @param \Closure $proceed
     * @param \Magento\Customer\Model\Customer $customer
     * @param $customerId
     * @return mixed|void
     */
    public function aroundLoad(\Magento\Customer\Model\ResourceModel\Customer $subject, \Closure $proceed, \Magento\Customer\Model\Customer $customer, $customerId)
    {
        /**
         * @todo check if salesrep_id is used for customer model
         */
        $baseResult = $proceed($customer, $customerId);

        if ($customer->isObjectNew()) {
            return;
        }

        $conn = $subject->getConnection();
        $select = $conn->select()
            ->from(['main_table' => $subject->getEntityTable()])
            ->where('main_table.entity_id = ?', $customerId)
            ->joinLeft(
                ['assigned_salesrep' => $this->resource->getTableName(\IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME)],
                'main_table.entity_id = assigned_salesrep.customer_id',
                [
                    self::KEY_ASSIGNED_SALES_REP => 'assigned_salesrep.' . AttachedCustomer::SALESREP_ID,
                    AttachedCustomer::COMMISSION_TYPE => 'assigned_salesrep.commission_type',
                    AttachedCustomer::COMMISSION_RATE => 'assigned_salesrep.commission_rate',
                    AttachedCustomer::COMMISSION_APPLY_WHEN => 'assigned_salesrep.commission_apply',
                ]
            )->joinLeft(
                ['salesrep_user' => $this->resource->getTableName(\IWD\SalesRep\Model\ResourceModel\B2BCustomer::TABLE_NAME)],
                'main_table.entity_id = salesrep_user.' . \IWD\SalesRep\Model\B2BCustomer::CUSTOMER_ID,
                [
                    self::KEY_SALESREP_ACCOUNT_ID => 'salesrep_user.' . \IWD\SalesRep\Model\B2BCustomer::SALESREP_ID,
                ]
            );

        if ($this->salesrepHelper->isWithB2B()) {
            $select->joinLeft(
                ['b2b_customer_info' => $this->resource->getTableName('iwd_b2b_customer_info')],
                'main_table.entity_id = b2b_customer_info.customer_id',
                []
            )->joinLeft(
                ['b2b_company' => $this->resource->getTableName('iwd_b2b_company')],
                'b2b_customer_info.company_id = b2b_company.company_id',
                [
                'store_name',
                ]
            );
        }

        $res = $conn->fetchRow($select);

        $toAdd = [
            self::KEY_ASSIGNED_SALES_REP,
            AttachedCustomer::COMMISSION_TYPE,
            AttachedCustomer::COMMISSION_RATE,
            AttachedCustomer::COMMISSION_APPLY_WHEN,
            self::KEY_SALESREP_ACCOUNT_ID,
            'store_name'
        ];

        foreach ($toAdd as $key) {
            if (isset($res[$key]) && $res[$key] !== null) {
                $customer->setData($key, $res[$key]);
            }
        }

        return $baseResult;
    }
}
