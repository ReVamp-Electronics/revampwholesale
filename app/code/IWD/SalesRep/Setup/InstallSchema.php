<?php

namespace IWD\SalesRep\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME)
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'enabled',
                Table::TYPE_SMALLINT,
                2,
                ['nullable' => false, 'default' => '0'],
                'Sales Rep account is enabled'
            )->addColumn(
                'admin_user_id',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => true, 'unique' => true, 'unsigned' => true],
                'Admin user ID'
            );

        $installer->getConnection()->createTable($table);

        $table1 = $installer->getConnection()
            ->newTable(
                $installer->getTable(\IWD\SalesRep\Model\ResourceModel\B2BCustomer::TABLE_NAME)
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
                ['unsigned' => true, 'nullable' => false, ],
                'Entity Id'
            )->addColumn(
                'website_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,],
                'Entity Id'
            );

        $installer->getConnection()->createTable($table1);

        $installer->endSetup();
    }
}
