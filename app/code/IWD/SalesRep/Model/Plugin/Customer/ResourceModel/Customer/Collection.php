<?php

namespace IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer;

use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Framework\Registry;
use IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer
 */
class Collection
{
    const KEY_ASSIGNED_SALESREP_ID = 'salesrep_id';
    const KEY_B2B_MASTER_SALESREP_ID = 'b2b_salesrep_id';

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory
     */
    private $b2bCustomerCollectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * Collection constructor.
     * @param Registry $registry
     * @param \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $b2bCustomerCollection
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Registry $registry,
        \IWD\SalesRep\Model\ResourceModel\B2BCustomer\CollectionFactory $b2bCustomerCollection,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->b2bCustomerCollectionFactory = $b2bCustomerCollection;
        $this->registry = $registry;
        $this->resourceConnection = $resource;
    }

    /**
     * @param CustomerCollection $subject
     * @return null
     */
    public function beforeLoad(CustomerCollection $subject)
    {
        $attachedCustomersTable = $this->resourceConnection->getTableName(\IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME);
        $masterB2BSalesRepAccountTable = $this->resourceConnection->getTableName(\IWD\SalesRep\Model\ResourceModel\B2BCustomer::TABLE_NAME);

        $select = $subject->getSelect();
        $joins = $select->getPart('from');

        // join sales rep, that is assigned to customer
        if (!isset($joins['assigned_salesrep'])) {
            $select->joinLeft(
                ['assigned_salesrep'=>$attachedCustomersTable],
                'e.entity_id = assigned_salesrep.customer_id',
                [
                    self::KEY_ASSIGNED_SALESREP_ID => 'assigned_salesrep.salesrep_id',
                    AttachedCustomer::COMMISSION_TYPE => 'assigned_salesrep.commission_type',
                    AttachedCustomer::COMMISSION_RATE => 'assigned_salesrep.commission_rate',
                    AttachedCustomer::COMMISSION_APPLY_WHEN => 'assigned_salesrep.commission_apply',
                ]
            );
        }
        // join 2b2 master admin's sales rep
        if (!isset($joins['b2b_salesrep'])) {
            $select->joinLeft(
                ['b2b_salesrep' => $masterB2BSalesRepAccountTable],
                'e.entity_id = b2b_salesrep.customer_id',
                [self::KEY_B2B_MASTER_SALESREP_ID => 'b2b_salesrep.salesrep_id']
            );
        }

        return null;
    }

    /**
     * @param CustomerCollection $subject
     * @param \Closure $proceed
     * @param $attribute
     * @param bool $joinType
     * @return CustomerCollection|mixed
     */
    public function aroundAddAttributeToSelect(CustomerCollection $subject, \Closure $proceed, $attribute, $joinType = false)
    {
        if ($attribute == 'name') {
            $subject->getSelect()->columns([$attribute => new \Zend_Db_Expr("CONCAT(firstname, ' ', lastname)")]);
            return $subject;
        }

        if ($attribute == 'admin_user') {
            $attachedCustomerTable = $this->resourceConnection->getTableName('iwd_sales_representative_attached_customer');
            $representativeUser = $this->resourceConnection->getTableName('iwd_sales_representative_user');
            $adminUserTable = $this->resourceConnection->getTableName('admin_user');
            
            $subject->getSelect()->joinLeft(
                ["attached_customer" => $attachedCustomerTable],
                "e.entity_id = attached_customer.customer_id",
                []
            )->joinLeft(
                ["representative_user" => $representativeUser],
                "representative_user.entity_id = attached_customer.salesrep_id",
                []
            )->joinLeft(
                ["admin_user" => $adminUserTable],
                "admin_user.user_id = representative_user.admin_user_id",
                []
            )->columns(
                [$attribute => \Zend_Db_Expr("CONCAT(admin_user.firstname, ' ', admin_user.lastname)")]
            );
            
            return $subject;
        }

        return $proceed($attribute, $joinType);
    }

    public function aroundAddFieldToFilter(CustomerCollection $subject, \Closure $proceed, $attribute, $condition = null)
    {
        $adminUser = $this->registry->registry('admin_user');
        $tempCollection = $this->b2bCustomerCollectionFactory->create();
        switch ($attribute) {
            case self::KEY_B2B_MASTER_SALESREP_ID:
                $condSql = $tempCollection->translateCondition($attribute, $condition);
                $condSql = str_replace('`'.$attribute.'`', '`b2b_salesrep`.`salesrep_id`', $condSql);
                $condSql = str_replace(" = 'NULL'", "is NULL", $condSql);
                $condSql = str_replace(" != 'NULL'", "is not NULL", $condSql);
                $subject->getSelect()->where($condSql);
                return $subject;
            case 'is_assigned':
                $cond = 'assigned_salesrep.salesrep_id';
                $salesrepId = $adminUser->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID);
                if ($condition) {
                    $cond .= ' = ' . $salesrepId;
                } else {
                    $cond .= (' != ' . $salesrepId . ' or ' . $cond . ' is null');
                }
                $subject->getSelect()->where($cond);
                return $subject;
            case 'name':
                $cond = [
                    $tempCollection->translateCondition('e.firstname', $condition),
                    $tempCollection->translateCondition('e.lastname', $condition)
                ];
                $subject->getSelect()->where(implode(' or ', $cond));
                return $subject;
            case 'admin_user':
                $cond = [
                    $tempCollection->translateCondition('admin_user.firstname', $condition),
                    $tempCollection->translateCondition('admin_user.lastname', $condition)
                ];
                $subject->getSelect()->where(implode(' or ', $cond));
                return $subject;
            default:
                return $proceed($attribute, $condition);
        }
    }
}
