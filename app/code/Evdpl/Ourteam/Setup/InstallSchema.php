<?php 
namespace Evdpl\Ourteam\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('ourteam_ourteam'))
            ->addColumn(
                'post_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Post ID'
            )
            ->addColumn('designation', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null])
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Ourteam Title')
            ->addColumn('image', Table::TYPE_TEXT, 255, ['nullable' => false], 'Ourteam Image')
            ->addColumn('content', Table::TYPE_TEXT, '2M', [], 'Ourteam Content')
            ->addColumn('displayorder',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,array('nullable' => true),'Display Order')
            ->addColumn('store_ids',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,255,array('nullable' => false),'Store Ids')
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Post Active?')
            ->addColumn('creation_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE], 'Update Time')
            ->addIndex($installer->getIdxName('ourteam_post', ['designation']), ['designation'])
            ->setComment('Evdpl Ourteam Posts');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}
