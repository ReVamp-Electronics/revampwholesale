<?php

namespace MW\RewardPoints\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use MW\RewardPoints\Model\Typerule;
use MW\RewardPoints\Model\Statusrule;
use MW\RewardPoints\Model\System\Config\Source\Applyrewardtax;

class ApplyRulesCronEvery
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
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogDataHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Helper\Rules
     */
    protected $_rulesHelper;

    /**
     * @var \MW\RewardPoints\Model\CatalogrulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @var \MW\RewardPoints\Model\ProductpointFactory
     */
    protected $_productpointFactory;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitConfig
     * @param \Magento\Catalog\Helper\Data $catalogDataHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Helper\Rules $rulesHelper
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     * @param \MW\RewardPoints\Model\ProductpointFactory $productpointFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitConfig,
        \Magento\Catalog\Helper\Data $catalogDataHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Helper\Rules $rulesHelper,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
        \MW\RewardPoints\Model\ProductpointFactory $productpointFactory
    ) {
        $this->_resource = $resource;
        $this->_config = $config;
        $this->_reinitConfig = $reinitConfig;
        $this->_catalogDataHelper = $catalogDataHelper;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_rulesHelper = $rulesHelper;
        $this->_catalogrulesFactory = $catalogrulesFactory;
        $this->_productpointFactory = $productpointFactory;
    }

    /**
     * Apply catalog rules by cronjob
     */
    public function execute()
    {
        if ($this->_dataHelper->getStoreConfig('mw_reward_last_id') != -1) {
            $lastProductId = 0;
            $limit = 500;

            if ($this->_dataHelper->getStoreConfig('mw_reward_last_id')) {
                $lastProductId = (int) $this->_dataHelper->getStoreConfig('mw_reward_last_id');
            }

            $productCollection = $this->_productFactory->create()->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['gt' => $lastProductId])
                ->setOrder('entity_id', 'ASC')
                ->setPageSize($limit)
                ->setCurPage(1);

            if (sizeof($productCollection) > 0) {
                $catalogruleIds = $this->_rulesHelper->getCatalogRules();

                if (sizeof($catalogruleIds) > 0) {
                    $storeCode = $this->_storeManager->getStore()->getCode();
                    $selectedCatalogrules = [];
                    $catalogrules = $this->_catalogrulesFactory->create()->getCollection()
                        ->addFieldToFilter('rule_id', ['in' => $catalogruleIds]);

                    if ($catalogrules->count() > 0) {
                        foreach ($catalogrules as $catalogrule) {
                            $ruleId       = $catalogrule->getRuleId();
                            $rewardpoint  = (int) $catalogrule->getRewardPoint();
                            $simpleAction = (int) $catalogrule->getSimpleAction();
                            $rewardStep   = (int) $catalogrule->getRewardStep();
                            $stopRule     = (int) $catalogrule->getStopRulesProcessing();
                            $selectedCatalogrules[] = $ruleId;

                            if ($rewardpoint > 0) {
                                foreach ($productCollection as $product) {
                                    $productId     = $product->getId();
                                    $lastProductId = $productId;
                                    $checkInserts  = $this->_productpointFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', $productId)
                                        ->addFieldToFilter('rule_id', $ruleId);

                                    if ($catalogrule->getConditions()->validate($product)) {
                                        $data               = [];
                                        $data['product_id'] = $productId;
                                        $data['rule_id']    = $ruleId;

                                        if ($simpleAction == Typerule::FIXED) {
                                            $data['reward_point'] = $rewardpoint;
                                        } else {
                                            $finalPrice = $product->getFinalPrice();
                                            $finalPriceExcludingTax = $this->_catalogDataHelper->getTaxPrice(
                                                $product,
                                                $product->getFinalPrice()
                                            );
                                            $data['reward_point'] = 0;

                                            if ($rewardStep > 0) {
                                                $applyRewardPointsTax = (int) $this->_dataHelper->getApplyRewardPointsTax($storeCode);
                                                if ($applyRewardPointsTax == Applyrewardtax::BEFORE) {
                                                    $data['reward_point'] = (int) ($finalPriceExcludingTax * $rewardpoint) / $rewardStep;
                                                } else {
                                                    $data['reward_point'] = (int) ($finalPrice * $rewardpoint) / $rewardStep;
                                                }
                                            }
                                        }

                                        if ($checkInserts->getSize() == 0) {
                                            if ($data['reward_point'] > 0) {
                                                $this->_productpointFactory->create()->setData($data)->save();
                                            }
                                        } else {
                                            foreach ($checkInserts as $checkInsert) {
                                                if ($data['reward_point'] > 0) {
                                                    $checkInsert->setRewardPoint($data['reward_point'])->save();
                                                } else if ($data['reward_point'] == 0) {
                                                    $checkInsert->delete();
                                                }
                                            }
                                        }
                                    } else {
                                        if ($checkInserts->getSize() > 0) {
                                            foreach ($checkInserts as $checkInsert) {
                                                $checkInsert->delete();
                                            }
                                        }
                                    }
                                }
                            }

                            if ($stopRule) {
                                $selectedCatalogrules = implode(",", $selectedCatalogrules);
                                if ($selectedCatalogrules == '') {
                                    $selectedCatalogrules = 0;
                                }

                                $writeConnection = $this->_resource->getConnection('write');
                                $table = $this->_resource->getTableName('mw_reward_product_point');
                                $query = "DELETE FROM " . $table . " WHERE rule_id NOT IN (" . $selectedCatalogrules . ")";
                                $writeConnection->query($query);
                                break;
                            }
                        }
                    }

                    $this->_config->saveConfig(
                        'mw_reward_last_id',
                        $lastProductId,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        0
                    );
                    $this->_reinitConfig->reinit();
                }
            }
        }

        if (sizeof($this->_productFactory->create()->getCollection()) <= $limit) {
            $this->_config->saveConfig(
                'mw_reward_last_id',
                -1,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
            $this->_reinitConfig->reinit();
        }
    }
}
