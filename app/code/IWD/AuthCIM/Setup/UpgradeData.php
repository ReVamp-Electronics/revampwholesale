<?php

namespace IWD\AuthCIM\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpgradeData
 * @package IWD\AuthCIM\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->updateCustomerAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * Update customer custom attribute
     *
     * @param \Magento\Framework\Setup\SetupInterface $setup
     */
    private function updateCustomerAttribute($setup)
    {
        try {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->updateAttribute(
                Customer::ENTITY,
                'iwd_authcim_profile_id',
                'is_system',
                false // <-- important, otherwise values aren't saved
            );
        } catch (\Exception $e) {
            $this->logger->critical('IWD CIM installation: ' . $e->getMessage());
        }
    }
}
