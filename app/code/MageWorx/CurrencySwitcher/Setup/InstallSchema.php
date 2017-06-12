<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Setup;

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
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'currencyswitcher_relations'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(\MageWorx\CurrencySwitcher\Model\Relations::ENTITY))
            ->addColumn(
                \MageWorx\CurrencySwitcher\Model\Relations::KEY_RELATION_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Relation ID'
            )
            ->addColumn(
                \MageWorx\CurrencySwitcher\Model\Relations::KEY_CURRENCY_CODE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                ['nullable' => false],
                'Currency Code'
            )
            ->addColumn(
                \MageWorx\CurrencySwitcher\Model\Relations::KEY_COUNTRIES,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Countries'
            )
            ->setComment('Currency Switcher Relations Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
