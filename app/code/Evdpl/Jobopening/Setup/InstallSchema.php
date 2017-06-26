<?php

namespace Evdpl\Jobopening\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

		$table = $installer->getConnection()->newTable(
			$installer->getTable('jobopening_jobopening')
		)->addColumn(
			'entity_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Job Opening ID'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Job Title'
		)->addColumn(
			'department',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['nullable' => true,'default' => null],
			'Department'
		)->addColumn(
			'description',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			null,
			['nullable' => true,'default' => null],
			'Job Description'
		)->addColumn(
			'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Status'
		)->addColumn(
		'store_ids',
		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,255,
		array('nullable' => false),
		'Store Ids')->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false],
			'Job Opening Creation Time'
		)->addColumn('update_time', 
		\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
		 null,
		 ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
		  'Update Time'
		  )->setComment(
			'JobOpening item'
		);
		$installer->getConnection()->createTable($table);
		$installer->endSetup();
	}
}