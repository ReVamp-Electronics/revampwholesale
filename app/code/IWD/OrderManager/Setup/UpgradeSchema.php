<?php

namespace IWD\OrderManager\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package IWD\OrderManager\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addLogTable($setup);
        }

        if (version_compare($context->getVersion(), '2.6.0', '<')) {
            $this->updateOrderHistoryTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addLogTable($setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('iwd_om_log'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'ID'
            )->addColumn(
                'admin_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Admin ID'
            )->addColumn(
                'admin_name',
                Table::TYPE_TEXT,
                64,
                [],
                'Admin Name'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Order ID'
            )->addColumn(
                'order_increment_id',
                Table::TYPE_TEXT,
                32,
                [],
                'Order Increment ID'
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                null,
                [],
                'Description'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addIndex(
                $setup->getIdxName('iwd_om_log', ['id']),
                ['id']
            )->setComment('IWD Order Manager Log Table');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function updateOrderHistoryTable($setup)
    {
        $columns = [
            'admin_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Admin Id',
                'default' => 0
            ],
            'admin_email' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '100',
                'nullable' => true,
                'comment' => 'Admin Email',
                'default' => ''
            ],
        ];

        $connection = $setup->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($setup->getTable('sales_order_status_history'), $name, $definition);
        }
    }
}
