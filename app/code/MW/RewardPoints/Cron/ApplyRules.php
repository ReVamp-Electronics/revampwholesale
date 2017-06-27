<?php

namespace MW\RewardPoints\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ApplyRules
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_reinitConfig;

    /**
     * @var \MW\RewardPoints\Helper\Rules
     */
    protected $_rulesHelper;

    /**
     * @var \MW\RewardPoints\Cron\ApplyRulesCronEvery
     */
    protected $_applyRulesCronEvery;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitConfig
     * @param \MW\RewardPoints\Helper\Rules $rulesHelper
     * @param \MW\RewardPoints\Cron\ApplyRulesCronEvery $applyRulesCronEvery
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitConfig,
        \MW\RewardPoints\Helper\Rules $rulesHelper,
        \MW\RewardPoints\Cron\ApplyRulesCronEvery $applyRulesCronEvery
    ) {
        $this->_resource = $resource;
        $this->_config = $config;
        $this->_reinitConfig = $reinitConfig;
        $this->_rulesHelper = $rulesHelper;
        $this->_applyRulesCronEvery = $applyRulesCronEvery;
    }

    /**
     * Run cron every day or apply rule manually
     */
    public function execute()
    {
        // Get catalog rule IDs which are valid then convert to string (Ex: 2,1,7)
        $catalogrules = implode(",", $this->_rulesHelper->getCatalogRules());
        if ($catalogrules == '') {
            $catalogrules = 0;
        }

        $writeConnection = $this->_resource->getConnection('write');
        $table = $this->_resource->getTableName('mw_reward_product_point');
        $query = "DELETE FROM " . $table . " WHERE rule_id not in (" . $catalogrules . ")";
        $writeConnection->query($query);

        // Save last ID of catalog rule
        $this->_config->saveConfig(
            'mw_reward_last_id',
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->_reinitConfig->reinit();

        $this->_applyRulesCronEvery->execute();
    }
}
