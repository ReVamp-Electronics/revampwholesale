<?php

namespace MW\RewardPoints\Model;

use MW\RewardPoints\Model\Statusrule;
use Magento\Catalog\Model\Product;

class Catalogrules extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Store current date at "Y-m-d H:i:s" format
     *
     * @var string
     */
    protected $_now;

    /**
     * Cached data of prices calculated by price rules
     *
     * @var array
     */
    protected static $_priceRulesData = [];

    /**
     * @var \MW\RewardPoints\Model\ProductpointFactory
     */
    protected $_productPointFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $_combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $_actionCollectionFactory;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypesList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Catalog rule data
     *
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $_catalogRuleData;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var array
     */
    protected $_relatedCacheTypes;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \MW\RewardPoints\Model\ProductpointFactory $productPointFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $relatedCacheTypes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \MW\RewardPoints\Model\ProductpointFactory $productPointFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
    	parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_cacheTypesList = $cacheTypesList;
        $this->_relatedCacheTypes = $relatedCacheTypes;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_productFactory = $productFactory;
        $this->_productPointFactory = $productPointFactory;
    }

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
        parent::_construct();
		$this->_init('MW\RewardPoints\Model\ResourceModel\Catalogrules');
        $this->setIdFieldName('rule_id');
	}

	/**
     * Getter for rule conditions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'name'        => $this->getName(),
            'start_at'    => $this->getStartAt(),
            'expire_at'   => $this->getExpireAt(),
            'description' => $this->getDescription(),
            'conditions'  => $this->getConditions()->asArray(),
            'actions'     => $this->getActions()->asArray(),
        ];

        return $out;
    }

    public function afterLoad()
    {
        $this->_afterLoad();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
    }

    /**
     * Prepare data before saving
     */
    public function beforeSave()
    {
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized(serialize($this->getActions()->asArray()));
            $this->unsActions();
        }

        /**
         * Prepare customer group Ids if applicable and if they were set as string in comma separated format.
         * Backwards compatibility.
         */
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        if (!$this->getId()) {
            $this->isObjectNew(true);
        }
        $this->_eventManager->dispatch('model_save_before', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());

        return $this;
    }

    /**
     * Returns rule as an array for admin interface
     *
     * Output example:
     * array(
     *   'name'=>'Example rule',
     *   'conditions'=>{condition_combine::toArray}
     *   'actions'=>{action_collection::toArray}
     * )
     *
     * @return array
     */
    public function toArray(array $arrAttributes = [])
    {
        $out                        = parent::toArray($arrAttributes);
        $out['customer_registered'] = $this->getCustomerRegistered();
        $out['customer_new_buyer']  = $this->getCustomerNewBuyer();

        return $out;
    }

    /**
     * Invalidate related cache types
     *
     * @return $this
     */
    protected function _invalidateCache()
    {
        if (count($this->_relatedCacheTypes)) {
            $this->_cacheTypesList->invalidate($this->_relatedCacheTypes);
        }
        return $this;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

    /**
     * Calculate price using catalog price rule of product
     *
     * @param Product $product
     * @param float $price
     * @return float|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function calcProductPriceRule(Product $product, $price)
    {
        $priceRules = null;
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $cacheKey = date('Y-m-d', $dateTs) . "|{$websiteId}|{$customerGroupId}|{$productId}|{$price}";

        if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
            $rulesData = $this->_getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
            if ($rulesData) {
                foreach ($rulesData as $ruleData) {
                    if ($product->getParentId()) {
                        if (!empty($ruleData['sub_simple_action'])) {
                            $priceRules = $this->_catalogRuleData->calcPriceRule(
                                $ruleData['sub_simple_action'],
                                $ruleData['sub_discount_amount'],
                                $priceRules ? $priceRules : $price
                            );
                        } else {
                            $priceRules = $priceRules ? $priceRules : $price;
                        }
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    } else {
                        $priceRules = $this->_catalogRuleData->calcPriceRule(
                            $ruleData['action_operator'],
                            $ruleData['action_amount'],
                            $priceRules ? $priceRules : $price
                        );
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    }
                }
                return self::$_priceRulesData[$cacheKey] = $priceRules;
            } else {
                self::$_priceRulesData[$cacheKey] = null;
            }
        } else {
            return self::$_priceRulesData[$cacheKey];
        }

        return null;
    }

    /**
     * Get rules from product
     *
     * @param string $dateTs
     * @param int $websiteId
     * @param array $customerGroupId
     * @param int $productId
     * @return array
     */
    protected function _getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId)
    {
        return $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
    }

    /**
     * Get reward points of catalog rule
     *
     * @param  int $productId
     * @return int
     */
    public function getPointCatalogRule($productId)
    {
        $rewardPointRule = 0;
        $rewardPointAttribute = 0;
        $collection = $this->_productPointFactory->create()->getCollection()
        	->addFieldToFilter('product_id', $productId);

        if ($collection->getSize() > 0) {
            foreach ($collection as $productPoint) {
                $ruleId = $productPoint->getRuleId();

                if ($ruleId != 0) {
                    $ruleModel 			= $this->load($ruleId);
                    $checkEnable        = $this->checkCatalogRulesByEnable($ruleModel);
                    $checkTime          = $this->checkCatalogRulesByTime($ruleModel);
                    $checkStoreView     = $this->checkCatalogRulesStoreView($ruleModel);
                    $checkCustomerGroup = $this->checkCatalogRulesCustomerGroup($ruleModel);

                    if ($checkEnable && $checkTime && $checkStoreView && $checkCustomerGroup) {
                        $rewardPointRule += (int) $productPoint->getRewardPoint();
                    }
                }
            }
        }

        $rewardPointProduct = $this->_productFactory->create()->load($productId)->getRewardPointProduct();
        if ($rewardPointProduct) {
            $rewardPointAttribute = (int) $rewardPointProduct;
        }
        if ($rewardPointAttribute) {
            return $rewardPointAttribute;
        } else {
            return $rewardPointRule;
        }
    }

    /**
     * Check catalog rule for time
     *
     * @param  \MW\RewardPoints\Model\Catalogrules $ruleModel
     * @return bool
     */
    public function checkCatalogRulesByTime($ruleModel)
    {
        if ($this->_localeDate->isScopeDateInInterval(
	        	null,
	        	$ruleModel->getStartDate(),
	        	$ruleModel->getEndDate()
	        )
        ) {
            return true;
        }

		return false;
    }

    /**
     * Check catalog rule for status
     *
     * @param  \MW\RewardPoints\Model\Catalogrules $ruleModel
     * @return bool
     */
    public function checkCatalogRulesByEnable($ruleModel)
    {
        if ($ruleModel->getStatus() == Statusrule::ENABLED) {
            return true;
        }

        return false;
    }

    /**
     * Check catalog rule for store view
     *
     * @param  \MW\RewardPoints\Model\Catalogrules $ruleModel
     * @return bool
     */
    public function checkCatalogRulesStoreView($ruleModel)
    {
        $storeId    = $this->_storeManager->getStore()->getId();
        $storeViews = explode(',', $ruleModel->getStoreView());
        if (in_array($storeId, $storeViews) || $storeViews[0] == '0') {
            return true;
        }

		return false;
    }

    /**
     * Check catalog rule for customer group
     *
     * @param  \MW\RewardPoints\Model\Catalogrules $ruleModel
     * @return bool
     */
    public function checkCatalogRulesCustomerGroup($ruleModel)
    {
    	$customerSession = $this->_customerSession;
        if ($customerSession->getCustomerGroupId()) {
            $groupId = $customerSession->getCustomerGroupId();
        } else {
            $groupId = 0;
        }

        $customerGroupIds = explode(',', $ruleModel->getCustomerGroupIds());
        if (in_array($groupId, $customerGroupIds)) {
            return true;
        }

		return false;
    }
}
