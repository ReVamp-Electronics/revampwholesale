<?php

namespace IWD\SalesRep\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class UpgradeSchema
 * @package IWD\SalesRep\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable(\IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME)
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'salesrep_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,],
                'Entity Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,],
                'Entity Id'
            );
            $installer->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $link = $installer->getConnection()
                ->newTable(
                    $installer->getTable(\IWD\SalesRep\Model\ResourceModel\Order::TABLE_NAME)
                )->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )->addColumn(
                    'salesrep_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Sales Rep Id'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Order Id'
                );
            $installer->getConnection()->createTable($link);
        }
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $table = $installer->getTable(\IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME);
            $installer->getConnection()
                ->addColumn(
                    $table,
                    'commission_type',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 16,
                        'default' => AttachedCustomer::COMMISSION_TYPE_FIXED,
                        'comment' => 'Sales Rep commission type'
                    ]
                );
            $installer->getConnection()->addColumn(
                $table,
                'commission_rate',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'scale' => 2,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'sales rep commission',
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'commission_apply',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 16,
                    'default' => AttachedCustomer::COMMISSION_APPLY_AFTER,
                    'comment' => 'Sales Rep commission apply before, or after discounts'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $table = $installer->getTable(\IWD\SalesRep\Model\ResourceModel\Order::TABLE_NAME);
            $installer->getConnection()
                ->addColumn(
                    $table,
                    'commission_type',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 16,
                        'default' => AttachedCustomer::COMMISSION_TYPE_FIXED,
                        'comment' => 'Sales Rep commission type'
                    ]
                );
            $installer->getConnection()->addColumn(
                $table,
                'commission_rate',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'scale' => 2,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'sales rep commission',
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'commission_apply',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 16,
                    'default' => AttachedCustomer::COMMISSION_APPLY_AFTER,
                    'comment' => 'Sales Rep commission apply before, or after discounts'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            // add indexes
            $attachedCustomerTable = \IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME;
            $installer->getConnection()->addIndex(
                $installer->getTable($attachedCustomerTable),
                $installer->getIdxName($attachedCustomerTable, ['salesrep_id']),
                'salesrep_id'
            );
            $installer->getConnection()->addIndex(
                $installer->getTable($attachedCustomerTable),
                $installer->getIdxName($attachedCustomerTable, ['customer_id']),
                'customer_id'
            );

            $b2bCustomerTable = $installer->getTable(\IWD\SalesRep\Model\ResourceModel\B2BCustomer::TABLE_NAME);
            $installer->getConnection()->addIndex(
                $installer->getTable($b2bCustomerTable),
                $installer->getIdxName($b2bCustomerTable, ['salesrep_id']),
                'salesrep_id'
            );
            $installer->getConnection()->addIndex(
                $installer->getTable($b2bCustomerTable),
                $installer->getIdxName($b2bCustomerTable, ['customer_id']),
                'customer_id'
            );
            $installer->getConnection()->addIndex(
                $installer->getTable($b2bCustomerTable),
                $installer->getIdxName($b2bCustomerTable, ['website_id']),
                'website_id'
            );

            $orderTable = $installer->getTable(\IWD\SalesRep\Model\ResourceModel\Order::TABLE_NAME);
            $installer->getConnection()->addIndex(
                $installer->getTable($orderTable),
                $installer->getIdxName($orderTable, ['salesrep_id']),
                'salesrep_id'
            );
            $installer->getConnection()->addIndex(
                $installer->getTable($orderTable),
                $installer->getIdxName($orderTable, ['order_id']),
                'order_id'
            );

            $salesrepUserTable = $installer->getTable(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME);
            $installer->getConnection()->addIndex(
                $installer->getTable($salesrepUserTable),
                $installer->getIdxName($salesrepUserTable, ['admin_user_id']),
                'admin_user_id'
            );
        }
    }
}
