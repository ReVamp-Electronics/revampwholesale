<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    public $eavSetupFactory;
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @since 1.1.0 Attribute Relation functional release */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1', '<')) {
            $this->changeRelationIdColumn($setup);
        }
        if (version_compare($context->getVersion(), '1.1', '<')) {
            $this->createRelationFlatGridTable($setup);
        }

        /* fix activated attribute type*/
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->changeActivatedAttribute();
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeRelationIdColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_customer_attributes_relation');
        if ($setup->getConnection()->tableColumnExists($tableName, 'id')) {
            $oldFkName = $setup->getFkName(
                'amasty_customer_attributes_details',
                'relation_id',
                'amasty_customer_attributes_relation',
                'id'
            );
            $setup->getConnection()->dropForeignKey($setup->getTable('amasty_customer_attributes_details'), $oldFkName);

            $setup->getConnection()->changeColumn(
                $tableName,
                'id',
                'relation_id',
                [
                    'IDENTITY' => true,
                    'UNSIGNED' => true,
                    'NULLABLE' => false,
                    'PRIMARY'  => true,
                    'TYPE'     => Table::TYPE_INTEGER,
                    'COMMENT' => 'Relation Id'
                ]
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName(
                    'amasty_customer_attributes_details',
                    'relation_id',
                    'amasty_customer_attributes_relation',
                    'relation_id'
                ),
                $setup->getTable('amasty_customer_attributes_details'),
                'relation_id',
                $setup->getTable('amasty_customer_attributes_relation'),
                'relation_id',
                Table::ACTION_CASCADE
            );
        }
    }

    /**
     * Create table 'amasty_customer_attributes_relation_grid'
     *
     * @param SchemaSetupInterface $installer
     */
    private function createRelationFlatGridTable(SchemaSetupInterface $installer)
    {
        if ($installer->tableExists($installer->getTable('amasty_customer_attributes_relation_grid'))) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_customer_attributes_relation_grid')
        )->addColumn(
            'relation_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Relation Id'
        )->addColumn(
            'relation_name',
            Table::TYPE_TEXT,
            255,
            [],
            'Relation Name'
        )->addColumn(
            'parent_attribute',
            Table::TYPE_TEXT,
            255,
            [],
            'Parent Attribute'
        )->addColumn(
            'dependent_attribute',
            Table::TYPE_TEXT,
            255,
            [],
            'Dependent Attributes'
        )->addColumn(
            'attribute_codes',
            Table::TYPE_TEXT,
            255,
            [],
            'Attribute Codes'
        )->setComment(
            'Relation Flat Grid'
        );

        $installer->getConnection()->createTable($table);
    }

    private function changeActivatedAttribute()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'am_is_activated');
        /*
         * we can add this attribute in new versions
        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'am_is_activated',
            [
                'type' => 'int',
                'label' => 'Is Active',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => true,
                'required' => false,
                'default_value' => 1,
                'adminhtml_only' => 0
            ]
        );*/
    }
}
