<?php

namespace IWD\MultiInventory\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package IWD\MultiInventory\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        //$this->migrateWarehouses($setup);
        //$this->migrateWarehouseItems($setup);
        //$this->deleteAdditionalInfoFromStandardTables($setup);

        $setup->endSetup();
    }

    /**
     * Migrate data from cataloginventory_stock to iwd_cataloginventory_stock
     *
     * @param \Magento\Framework\Setup\SetupInterface $setup
     */
    private function migrateWarehouses($setup)
    {
        $cataloginventoryStockTable = $setup->getTable('cataloginventory_stock');
        $iwdCataloginventoryStockTable = $setup->getTable('iwd_cataloginventory_stock');
        $id = \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID;

        $select = $setup->getConnection()->select()
            ->from(
                $cataloginventoryStockTable,
                ['stock_id', 'website_id', 'stock_name']
            )->where('stock_id != ' . $id);

        $stocks = $setup->getConnection()->fetchAll($select);
        foreach ($stocks as $stock) {
            $setup->getConnection()->insert($iwdCataloginventoryStockTable, $stock);
        }
    }

    /**
     * Migrate data from cataloginventory_stock_item to iwd_cataloginventory_stock_item
     *
     * @param \Magento\Framework\Setup\SetupInterface $setup
     */
    private function migrateWarehouseItems($setup)
    {
        $cataloginventoryStockItemTable = $setup->getTable('cataloginventory_stock_item');
        $iwdCataloginventoryStockItemTable = $setup->getTable('iwd_cataloginventory_stock_item');
        $id = \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID;

        $select = $setup->getConnection()->select()
            ->from(
                $cataloginventoryStockItemTable,
                ['item_id', 'product_id', 'stock_id', 'qty', 'is_in_stock', 'manage_stock', 'website_id']
            )->where('stock_id != ' . $id);

        $stockItems = $setup->getConnection()->fetchAll($select);
        foreach ($stockItems as $item) {
            $setup->getConnection()->insert($iwdCataloginventoryStockItemTable, $item);
        }
    }

    /**
     * Delete data from cataloginventory_stock and cataloginventory_stock_item added via Order Manager
     *
     * @param \Magento\Framework\Setup\SetupInterface $setup
     */
    private function deleteAdditionalInfoFromStandardTables($setup)
    {
        $cataloginventoryStockTable = $setup->getTable('cataloginventory_stock');
        $cataloginventoryStockItemTable = $setup->getTable('cataloginventory_stock_item');
        $cataloginventoryStockStatusTable = $setup->getTable('cataloginventory_stock_status');
        $cataloginventoryStockStatusIdxTable = $setup->getTable('cataloginventory_stock_status_idx');

        $id = \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID;

        $setup->getConnection()->delete($cataloginventoryStockTable, ['stock_id != (?)' => $id]);
        $setup->getConnection()->delete($cataloginventoryStockItemTable, ['stock_id != (?)' => $id]);
        $setup->getConnection()->delete($cataloginventoryStockStatusTable, ['stock_id != (?)' => $id]);
        $setup->getConnection()->delete($cataloginventoryStockStatusIdxTable, ['stock_id != (?)' => $id]);
    }
}
