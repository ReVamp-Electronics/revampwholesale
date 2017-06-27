<?php

namespace IWD\AuthCIM\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
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
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(EavSetupFactory $eavSetupFactory, LoggerInterface $logger)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->updateCustomerAttributes($setup);
        $setup->endSetup();
    }

    /**
     * Add attribute to customer for save Authorize.net CIM profile ID
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function updateCustomerAttributes(ModuleDataSetupInterface $setup)
    {
        try {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Customer::ENTITY,
                'iwd_authcim_profile_id',
                [
                    'type' => 'varchar',
                    'label' => 'IWD Authorize.NET CIM Profile ID',
                    'input' => 'text',
                    'source' => '',
                    'visible' => false,
                    'required' => false,
                    'default' => '0',
                    'frontend' => "",
                    'unique' => false,
                    'note' => ""
                ]
            );
        } catch (\Exception $e) {
            $this->logger->critical('IWD CIM installation: ' . $e->getMessage());
        }
    }
}
