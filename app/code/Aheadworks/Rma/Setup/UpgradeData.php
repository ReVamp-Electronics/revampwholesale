<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->upgradeCustomFieldOptions($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeCustomFieldOptions($setup)
    {
        $rmaCustomFieldTable = $setup->getTable('aw_rma_custom_field');
        $select = $setup->getConnection()->select()->from(
            $rmaCustomFieldTable,
            ['id']
        )->where(
            'is_system = ?',
            1
        );

        $rmaCustomFieldOptionTable = $setup->getTable('aw_rma_custom_field_option');
        $customFields = $setup->getConnection()->fetchAll($select);
        foreach ($customFields as $field) {
            $bind = ['enabled' => 1];
            $where = ['field_id = ?' => (int)$field['id']];
            $setup->getConnection()->update($rmaCustomFieldOptionTable, $bind, $where);
        }
    }
}