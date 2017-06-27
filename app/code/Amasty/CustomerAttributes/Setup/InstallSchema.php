<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'used_in_product_listing',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Is Used In Product Listing'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'store_ids',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Store Ids'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'sorting_order',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Sorting Order'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'is_visible_on_front',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Is Visible On Front'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'type_internal',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Type Internal'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'on_order_view',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'On Order View'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'on_registration',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'On Registration'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'is_read_only',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Is Read Only'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'used_in_order_grid',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Used In Order Grid'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'file_size',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'File Size'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'file_types',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'File Types'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'file_dimensions',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'File Dimensions'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'account_filled',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Account Filled'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'billing_filled',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Billing Filled'
            ]
        );
        $connection->addColumn(
            $installer->getTable('customer_eav_attribute'),
            'required_on_front',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Required On Front'
            ]
        );

        $connection->addColumn(
            $installer->getTable('eav_attribute_option'),
            'group_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Group Id'
            ]
        );

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_customer_attributes_relation'))
            ->addColumn(
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Relation Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'defalut' => ''],
                'Name'
            )
            ->setComment('Amasty Customer Attributes Relation');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_customer_attributes_details'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Details Id'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Attribute Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Option Id'
            )
            ->addColumn(
                'dependent_attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Dependent Attribute Id'
            )
            ->addColumn(
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Relation Id'
            )->addIndex(
                $installer->getIdxName('amasty_customer_attributes_details', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('amasty_customer_attributes_details', ['dependent_attribute_id']),
                ['dependent_attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('amasty_customer_attributes_details', ['relation_id']),
                ['relation_id']
            )
            ->addForeignKey(
                $installer->getFkName('amasty_customer_attributes_details', 'attribute_id', 'eav_attribute_option', 'attribute_id'),
                'attribute_id',
                $installer->getTable('eav_attribute_option'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_customer_attributes_details', 'dependent_attribute_id', 'eav_attribute', 'attribute_id'),
                'dependent_attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_customer_attributes_details', 'relation_id', 'amasty_customer_attributes_relation', 'relation_id'),
                'relation_id',
                $installer->getTable('amasty_customer_attributes_relation'),
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Amasty Customer Attributes Relation Details');

        $installer->getConnection()->createTable($table);


        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_customer_attributes_guest'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order Id'
            )
            ->addIndex(
                $installer->getIdxName('amasty_customer_attributes_guest', ['order_id']),
                ['order_id']
            )
            ->setComment('Amasty Customer Attributes Guest');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
