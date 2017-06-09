<?php

namespace IWD\OrderManager\Setup;

use IWD\OrderManager\Model\LogFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package IWD\OrderManager\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @param LogFactory $logFactory
     */
    public function __construct(
        LogFactory $logFactory
    ) {
        $this->logFactory = $logFactory;
    }

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

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addDataToLogTable();
        }

        $setup->endSetup();
    }

    /**
     * Add Data To Log Table
     *
     * @return void
     */
    private function addDataToLogTable()
    {
        $description = 'Congratulations! IWD\'s Order Manager was installed successfully.';
        $this->logFactory->create()
            ->setDescription(__($description))
            ->save();
    }
}
