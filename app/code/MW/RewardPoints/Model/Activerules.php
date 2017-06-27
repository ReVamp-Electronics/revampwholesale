<?php

namespace MW\RewardPoints\Model;

use MW\RewardPoints\Model\Statusrule;

class Activerules extends \Magento\Framework\Model\AbstractModel
{
	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
	public function __construct(
		\Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
	) {
		parent::__construct($context, $registry, $resource, $resourceCollection);
		$this->_dataHelper = $dataHelper;
		$this->_storeManager = $storeManager;
	}

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('MW\RewardPoints\Model\ResourceModel\Activerules');
	}

	/**
	 * Get rule ID by coupon code
	 *
	 * @param  string $couponCode
	 * @return int
	 */
	public function getRuleIdbyCouponCode($couponCode)
    {
        $result = 0;

        if ($couponCode != '' && isset($couponCode)) {
            $collection = $this->getCollection()
                ->addFieldToFilter('coupon_code', $couponCode)
                ->addFieldToFilter('status', Statusrule::ENABLED);
            $activePoint = $collection->getFirstItem();

            if (sizeof($activePoint) > 0) {
                $result = $activePoint->getRuleId();
            }
        }

        return $result;
    }

    /**
     * Get reward points by rule ID (not customer group)
     *
     * @param  int $ruleId
     * @param  int $storeId
     * @return int
     */
    public function getPointByRuleIdNotGroup($ruleId, $storeId = null)
    {
        $result = 0;
        $activePoint = $this->load($ruleId);

        if ($activePoint->getRuleId() && $activePoint->getStatus() == Statusrule::ENABLED) {
            $rewardpoint    = $activePoint->getRewardPoint();
            $storeView      = $activePoint->getStoreView();
            $checkStoreView = $this->checkActiveRulesStoreViewNew($storeView, $storeId);

            if ($checkStoreView) {
                $result = $rewardpoint;
            }
        }

        return $result;
    }

    /**
     * Get reward points by rule ID
     *
     * @param  int $ruleId
     * @param  int $customerGroupId
     * @param  int $storeId
     * @return array
     */
    public function getPointByRuleId($ruleId, $customerGroupId, $storeId = null)
    {
        $rewardpoint 	= 0;
        $results 		= [];
        $expiredDay     = 0;
        $expiredTime    = null;
        $remainingPoint = 0;
        $activePoint    = $this->load($ruleId);

        if ($activePoint->getRuleId() && $activePoint->getStatus() == Statusrule::ENABLED) {
            $defaultExpired     = $activePoint->getDefaultExpired();
            $expiredDay         = $activePoint->getExpiredDay();
            $storeView          = $activePoint->getStoreView();
            $customerGroupIds 	= $activePoint->getCustomerGroupIds();
            $checkStoreView     = $this->checkActiveRulesStoreViewNew($storeView, $storeId);
            $checkCustomerGroup = $this->checkCustomerGroup($customerGroupIds, $customerGroupId);

            if ($checkStoreView && $checkCustomerGroup) {
                $rewardpoint = $activePoint->getRewardPoint();

                if ($defaultExpired == 1) {
                	$store = $this->_dataHelper->getStoreById($storeId);
                	if ($store) {
                		$storeCode = $store->getId();
                	} else {
                		$storeCode = null;
                	}

                    $expiredDay = (int) $this->_dataHelper->getExpirationDaysPoint($storeCode);
                }
            }
        }

        if ($expiredDay > 0) {
            $expiredTime    = time() + $expiredDay * 24 * 3600;
            $remainingPoint = $rewardpoint;
        }

        $results[0] = $rewardpoint;
        $results[1] = $expiredDay;
        $results[2] = $expiredTime;
        $results[3] = $remainingPoint;

        return $results;
    }

    /**
     * Get rule ID of custom rule
     *
     * @param  string $hashedRuleId
     * @return int
     */
    public function getRuleIdCustomRule($hashedRuleId)
    {
        $result = 0;

        $collection = $this->getCollection()->addFieldToFilter('status', Statusrule::ENABLED);
        $collection->getSelect()->where("md5(rule_id)='" . trim($hashedRuleId) . "'");
        $activePoint = $collection->getFirstItem();

        if (sizeof($activePoint) > 0) {
            $result = $activePoint->getRuleId();
        }

        return $result;
    }

    /**
     * Get reward points of custom rules
     *
     * @param  string 	$hashedRuleId (MD5)
     * @param  int 		$customerGroupId
     * @param  int 		$storeId
     * @return array
     */
    public function getPointCustomRules($hashedRuleId, $customerGroupId, $storeId = null)
    {
        $results        = [];
        $rewardpoint 	= 0;
        $expiredDay     = 0;
        $expiredTime    = null;
        $remainingPoint = 0;

        $collection = $this->getCollection()->addFieldToFilter('status', Statusrule::ENABLED);
        $collection->getSelect()->where("md5(rule_id)='" . trim($hashedRuleId) . "'");
        $activePoint = $collection->getFirstItem();

        if (sizeof($activePoint) > 0) {
            $defaultExpired     = $activePoint->getDefaultExpired();
            $expiredDay         = $activePoint->getExpiredDay();
            $storeView          = $activePoint->getStoreView();
            $customerGroupIds   = $activePoint->getCustomerGroupIds();
            $checkstoreView     = $this->checkActiveRulesStoreView($storeView, $storeId);
            $checkCustomerGroup = $this->checkCustomerGroup($customerGroupIds, $customerGroupId);

            if ($checkstoreView && $checkCustomerGroup) {
                $rewardpoint = $activePoint->getRewardPoint();

                if ($defaultExpired == 1) {
                	$store = $this->_dataHelper->getStoreById($storeId);
                	if ($store) {
                		$storeCode = $store->getId();
                	} else {
                		$storeCode = null;
                	}

                    $expiredDay = (int) $this->_dataHelper->getExpirationDaysPoint($storeCode);
                }
            }
        }

        if ($expiredDay > 0) {
            $expiredTime    = time() + $expiredDay * 24 * 3600;
            $remainingPoint = $rewardpoint;
        }

        $results[0] = $rewardpoint;
        $results[1] = $expiredDay;
        $results[2] = $expiredTime;
        $results[3] = $remainingPoint;

        return $results;
    }

    /**
     * Get data of active rules which expried points
     *
     * @param  int $typeOfTransaction
     * @param  int $customerGroupId
     * @param  int $storeId
     * @return array
     */
    public function getResultActiveRulesExpiredPoints($typeOfTransaction, $customerGroupId, $storeId = null)
    {
        $results        = [];
        $result         = $this->getPointActiveRulesExpiredPoints($typeOfTransaction, $customerGroupId, $storeId);
        $expiredTime    = null;
        $remainingPoint = 0;
        $points         = (int) $result[0];
        $expiredDay     = (int) $result[1];

        if ($expiredDay > 0) {
            $expiredTime    = time() + $expiredDay * 24 * 3600;
            $remainingPoint = $points;
        }

        $results[0] = $points;
        $results[1] = $expiredDay;
        $results[2] = $expiredTime;
        $results[3] = $remainingPoint;

        return $results;
    }

    /**
     * Get reward points of active rules which expried points
     *
     * @param  int $typeOfTransaction
     * @param  int $customerGroupId
     * @param  int $storeId
     * @return array
     */
    public function getPointActiveRulesExpiredPoints($typeOfTransaction, $customerGroupId, $storeId = null)
    {
        $result    = [0, 0];
        $collection = $this->getCollection()
            ->addFieldToFilter('type_of_transaction', $typeOfTransaction)
            ->addFieldToFilter('status', Statusrule::ENABLED);

        if (sizeof($collection) > 0) {
            foreach ($collection as $activePoint) {
                $defaultExpired     = $activePoint->getDefaultExpired();
                $expiredDay         = $activePoint->getExpiredDay();
                $rewardpoint        = $activePoint->getRewardPoint();
                $storeView          = $activePoint->getStoreView();
                $customerGroupIds   = $activePoint->getCustomerGroupIds();
                $checkStoreView     = $this->checkActiveRulesStoreView($storeView, $storeId);
                $checkCustomerGroup = $this->checkCustomerGroup($customerGroupIds, $customerGroupId);

                if ($checkStoreView && $checkCustomerGroup) {
                    if ($defaultExpired == 1) {
                    	$store = $this->_dataHelper->getStoreById($storeId);
	                	if ($store) {
	                		$storeCode = $store->getId();
	                	} else {
	                		$storeCode = null;
	                	}

                        $expiredDay = (int) $this->_dataHelper->getExpirationDaysPoint($storeCode);
                    }

                    $result[0] = $rewardpoint;
                    $result[1] = $expiredDay;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get reward points of active rules
     *
     * @param  int $typeOfTransaction
     * @param  int $customerGroupId
     * @param  int $storeId
     * @return int
     */
    public function getPointActiveRules($typeOfTransaction, $customerGroupId, $storeId = null)
    {
        $rewardpoint = 0;
        $collection = $this->getCollection()
            ->addFieldToFilter('type_of_transaction', $typeOfTransaction)
            ->addFieldToFilter('status', Statusrule::ENABLED);

        if (sizeof($collection) > 0) {
            foreach ($collection as $activePoint) {
                $storeView          = $activePoint->getStoreView();
                $customerGroupIds   = $activePoint->getCustomerGroupIds();
                $checkStoreView     = $this->checkActiveRulesStoreView($storeView, $storeId);
                $checkCustomerGroup = $this->checkCustomerGroup($customerGroupIds, $customerGroupId);

                if ($checkStoreView && $checkCustomerGroup) {
                    $rewardpoint = $activePoint->getRewardPoint();
                    break;
                }
            }
        }

        return $rewardpoint;
    }

    /**
     * Check customer group belongs customer groups of active rules
     *
     * @param  string 	$customerGroupIds
     * @param  int 		$customerGroupId
     * @return bool
     */
    public function checkCustomerGroup($customerGroupIds, $customerGroupId)
    {
        $customerGroupIds = explode(',', $customerGroupIds);
        if (in_array($customerGroupId, $customerGroupIds)) {
            return true;
        }

        return false;
    }

    /**
     * Check active rules by store view
     *
     * @param  array 	$storeView
     * @param  int 		$storeId
     * @return bool
     */
    public function checkActiveRulesStoreView($storeView, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        if (in_array($storeId, $storeView) || $storeView[0] == '0') {
            return true;
        }

		return false;
    }

    /**
     * Check active rules by store view
     *
     * @param  string 	$storeView
     * @param  int 		$storeId
     * @return bool
     */
    public function checkActiveRulesStoreViewNew($storeView, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        $storeView = explode(',', $storeView);
        if (in_array($storeId, $storeView) || $storeView[0] == '0') {
            return true;
        }

        return false;
    }
}
