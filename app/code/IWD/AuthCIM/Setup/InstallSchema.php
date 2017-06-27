<?php

namespace IWD\AuthCIM\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;

/**
 * Class InstallSchema
 * @package IWD\AuthCIM\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallSchema constructor.
     *
     * @param LoggerInterface $logger
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        LoggerInterface $logger,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->logger = $logger;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->addCardTable($installer);
        $this->addRefundsTable($installer);

        $installer->endSetup();
    }

    /**
     * Add table iwd_authorizecim_card
     *
     * @param $installer
     */
    private function addCardTable(SchemaSetupInterface $installer)
    {
        try {
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('iwd_authorizecim_card')
                )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'hash',
                    Table::TYPE_TEXT,
                    128,
                    ['nullable' => false, 'default' => '0'],
                    'Cart hash'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Customer ID'
                )
                ->addColumn(
                    'customer_email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'unique' => false],
                    'Customer Email'
                )
                ->addColumn(
                    'customer_ip',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => true, 'unique' => false],
                    'Customer IP'
                )
                ->addColumn(
                    'profile_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Authorize.net CIM Customer Profile Id'
                )
                ->addColumn(
                    'payment_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Authorize.net CIM Payment Id'
                )
                ->addColumn(
                    'method',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => true, 'unique' => false],
                    'Payment Method Code'
                )
                ->addColumn(
                    'active',
                    Table::TYPE_INTEGER,
                    2,
                    ['nullable' => true, 'unique' => false],
                    'Active'
                )
                ->addColumn(
                    'last_use',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Last Use Date'
                )
                ->addColumn(
                    'expires',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Expiration Date'
                )
                ->addColumn(
                    'address',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Address'
                )
                ->addColumn(
                    'additional',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Additional Data'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Created At Date'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Updated At Date'
                );

            $installer->getConnection()->createTable($table);
        } catch (\Exception $e) {
            $this->logger->critical('IWD CIM installation: ' . $e->getMessage());
        }
    }

    /**
     * Add table iwd_authorizecim_refunds
     *
     * @param $installer SchemaSetupInterface
     */
    private function addRefundsTable(SchemaSetupInterface $installer)
    {
        try {
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('iwd_authorizecim_refunds')
                )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'payment_id',
                    Table::TYPE_INTEGER,
                    10,
                    [],
                    'Payment Id'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_FLOAT,
                    '12,4',
                    [],
                    'Amount'
                );

            $installer->getConnection()->createTable($table);
        } catch (\Exception $e) {
            $this->logger->critical('IWD CIM installation: ' . $e->getMessage());
        }
    }
}
