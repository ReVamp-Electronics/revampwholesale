<?php

namespace IWD\MultiInventory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package IWD\MultiInventory\Setup
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

        $this->addWarehouseAddressTable($setup);
        $this->addColumnsToOrderTable($setup);
        $this->createStockOrderItemTable($setup);
        $this->addWarehouseTable($setup);
        $this->addWarehouseItemsTable($setup);
        $this->updateStockAddressIndex($setup);
        $this->updateCataloginventoryStockOrderItemIndex($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addWarehouseAddressTable($setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('iwd_cataloginventory_stock_address'))
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
                'stock_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Stock ID'
            )
            ->addColumn(
                'street',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Street'
            )->addColumn(
                'street',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Street'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'City'
            )->addColumn(
                'region_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Region Id'
            )->addColumn(
                'region',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Region'
            )->addColumn(
                'postcode',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Postcode'
            )->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                2,
                ['nullable' => true],
                'Country Id'
            )
            ->addIndex(
                $setup->getIdxName('iwd_cataloginventory_stock_address', ['stock_id']),
                ['stock_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    'iwd_cataloginventory_stock_address',
                    'stock_id',
                    'cataloginventory_stock',
                    'stock_id'
                ),
                'stock_id',
                $setup->getTable('cataloginventory_stock'),
                'stock_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('IWD Order Manager CatalogInventory Stock Address');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addColumnsToOrderTable($setup)
    {
        $columns = [
            'iwd_qty_assigned' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'size' => '12,4',
                'nullable' => true,
                'comment' => 'Qty Assigned',
                'default' => 0
            ],
            'iwd_stock_assigned' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Is Stock Assigned',
                'default' => -2 //Order Placed Before
            ],
        ];

        $connection = $setup->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($setup->getTable('sales_order'), $name, $definition);
            $connection->addColumn($setup->getTable('sales_order_grid'), $name, $definition);
        }

        $connection->modifyColumn(
            $setup->getTable('sales_order'),
            'iwd_stock_assigned',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Is Stock Assigned',
                'default' => 0
            ]
        );

        $connection->modifyColumn(
            $setup->getTable('sales_order_grid'),
            'iwd_stock_assigned',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Is Stock Assigned',
                'default' => 0
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function createStockOrderItemTable($setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('iwd_cataloginventory_stock_order_item'))
            ->addColumn(
                'stock_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Stock ID'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Order ID'
            )->addColumn(
                'order_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Order Item ID'
            )->addColumn(
                'qty_stock_assigned',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Qty Stock Assigned'
            )->addForeignKey(
                $setup->getFkName(
                    'iwd_cataloginventory_stock_order_item',
                    'stock_id',
                    'cataloginventory_stock',
                    'stock_id'
                ),
                'stock_id',
                $setup->getTable('cataloginventory_stock'),
                'stock_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    'iwd_cataloginventory_stock_order_item',
                    'order_item_id',
                    'sales_order_item',
                    'item_id'
                ),
                'order_item_id',
                $setup->getTable('sales_order_item'),
                'item_id',
                Table::ACTION_CASCADE
            )->setComment('IWD Order Manager CatalogInventory Stock Order Item');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addWarehouseTable($setup)
    {
        /**
         * Create table 'iwd_cataloginventory_stock'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('iwd_cataloginventory_stock'))
            ->addColumn(
                'stock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stock Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'stock_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Stock Name'
            )
            ->addIndex(
                $setup->getIdxName(
                    $setup->getTable('iwd_cataloginventory_stock'),
                    ['website_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['website_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
            ->setOption('auto_increment', 2)
            ->setComment('IWD Cataloginventory Additional Stocks');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addWarehouseItemsTable($setup)
    {
        $installer = $setup;

        /**
         * Create table 'iwd_cataloginventory_stock_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('iwd_cataloginventory_stock_item'))
            ->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Item Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )
            ->addColumn(
                'stock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Stock Id'
            )
            ->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'Qty'
            )
            ->addColumn(
                'is_in_stock',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is In Stock'
            )
            ->addColumn(
                'manage_stock',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Manage Stock'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Is Divided into Multiple Boxes for Shipping'
            )
            ->addIndex(
                $installer->getIdxName(
                    'iwd_cataloginventory_stock_item',
                    ['product_id', 'website_id', 'stock_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id', 'website_id', 'stock_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    'iwd_cataloginventory_stock_item',
                    ['website_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['website_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName('iwd_cataloginventory_stock_item', ['stock_id']),
                ['stock_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'iwd_cataloginventory_stock_item',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'iwd_cataloginventory_stock_item',
                    'stock_id',
                    'iwd_cataloginventory_stock',
                    'stock_id'
                ),
                'stock_id',
                $installer->getTable('iwd_cataloginventory_stock'),
                'stock_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('IWD Cataloginventory Additional Stock Item');

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function updateStockAddressIndex($setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $connection->dropForeignKey(
            $installer->getTable('iwd_cataloginventory_stock_address'),
            $installer->getFkName(
                'iwd_cataloginventory_stock_address',
                'stock_id',
                'cataloginventory_stock',
                'stock_id'
            )
        );

        $connection->dropIndex(
            $setup->getTable('iwd_cataloginventory_stock_address'),
            $installer->getIdxName('iwd_cataloginventory_stock_address', ['stock_id'])
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function updateCataloginventoryStockOrderItemIndex($setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $connection->dropForeignKey(
            $installer->getTable('iwd_cataloginventory_stock_order_item'),
            $installer->getFkName(
                'iwd_cataloginventory_stock_order_item',
                'stock_id',
                'cataloginventory_stock',
                'stock_id'
            )
        );

        $connection->addForeignKey(
            $setup->getFkName(
                'iwd_cataloginventory_stock_order_item',
                'stock_id',
                'iwd_cataloginventory_stock',
                'stock_id'
            ),
            $setup->getTable('iwd_cataloginventory_stock_order_item'),
            'stock_id',
            $setup->getTable('iwd_cataloginventory_stock'),
            'stock_id',
            Table::ACTION_CASCADE
        );
    }
}
