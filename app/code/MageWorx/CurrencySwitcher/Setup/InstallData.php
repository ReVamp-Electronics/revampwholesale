<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var \MageWorx\CurrencySwitcher\Model\Relations
     */
    protected $modelRelations;
    
    /**
     * Init
     *
     * @param \MageWorx\CurrencySwitcher\Model\Relations $modelRelations
     */
    public function __construct(
        \MageWorx\CurrencySwitcher\Model\Relations $modelRelations
    ) {
    
        $this->modelRelations = $modelRelations;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->modelRelations->refreshRelations();
    }
}
