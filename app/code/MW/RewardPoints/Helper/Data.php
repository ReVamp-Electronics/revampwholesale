<?php

namespace MW\RewardPoints\Helper;

use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use MW\RewardPoints\Model\System\Config\Source\Position;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const MYCONFIG = 'rewardpoints/general/enabled';

	const MYNAME = 'MW_RewardPoints';

	/**
	 * @var \Magento\Framework\App\Config\ReinitableConfigInterface
	 */
	protected $_reinitConfig;

	/**
	 * @var \Magento\Framework\Module\ModuleList
	 */
	protected $_moduleList;

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfig;

	/**
	 * @var \Magento\Config\Model\ResourceModel\Config
	 */
	protected $_config;

	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $_defaultCustomerFactory;

	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @var \MW\RewardPoints\Model\Type
	 */
	protected $_type;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	 */
	protected $_localeDate;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_dateTime;

	/**
	 * @var \Magento\Framework\Mail\Template\TransportBuilder
	 */
	protected $_transportBuilder;

	/**
	 * @var \Magento\Framework\Pricing\Helper\Data
	 */
	protected $_pricingHelper;

	/**
	 * @var \Magento\Store\Model\StoreFactory
	 */
	protected $_storeFactory;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Framework\View\Element\Template
	 */
	protected $_block;

	/**
	 * @var \MW\RewardPoints\Model\ProductpointFactory
	 */
	protected $_productpointFactory;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @var \Magento\Framework\Translate\Inline\StateInterface
	 */
	protected $_inlineTranslation;

	/**
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $_messageManager;

	/**
	 * @var \MW\RewardPoints\Model\ActiverulesFactory
	 */
	protected $_activerulesFactory;

	/**
	 * @var \MW\RewardPoints\Model\CatalogrulesFactory
	 */
	protected $_catalogrulesFactory;

	/**
	 * @var \MW\RewardPoints\Model\CartrulesFactory
	 */
	protected $_cartrulesFactory;

	/**
	 * @var \MW\RewardPoints\Model\ProductsellpointFactory
	 */
	protected $_productsellpointFactory;

	/**
	 * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
	 */
	protected $_historyFactory;

	/**
	 * @var \Magento\Framework\Session\SessionManagerInterface
	 */
	protected $_sessionManager;

	/**
	 * @var \Magento\Checkout\Model\Cart
	 */
	protected $_checkoutCart;

	/**
	 * @var \Magento\Framework\Pricing\PriceCurrencyInterface
	 */
	protected $_priceCurrency;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitConfig
	 * @param \Magento\Framework\Module\ModuleList $moduleList
	 * @param ScopeConfigInterface $scopeConfig
	 * @param \Magento\Config\Model\ResourceModel\Config $config
	 * @param \Magento\Customer\Model\CustomerFactory $defaultCustomerFactory
	 * @param \MW\RewardPoints\Model\CustomerFactory $customerFactory
	 * @param \MW\RewardPoints\Model\Type $type
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
	 * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	 * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	 * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
	 * @param \Magento\Store\Model\StoreFactory $storeFactory
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \MW\RewardPoints\Model\ProductpointFactory $productpointFactory
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
	 * @param \Magento\Framework\Message\ManagerInterface $messageManager
	 * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
	 * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
	 * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
	 * @param \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
	 * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
	 * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
	 * @param \Magento\Checkout\Model\Cart $checkoutCart
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
	 * @param \Magento\Framework\View\Element\Template $block
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\Config\ReinitableConfigInterface $reinitConfig,
		\Magento\Framework\Module\ModuleList $moduleList,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Config\Model\ResourceModel\Config $config,
		\Magento\Customer\Model\CustomerFactory $defaultCustomerFactory,
		\MW\RewardPoints\Model\CustomerFactory $customerFactory,
		\MW\RewardPoints\Model\Type $type,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Pricing\Helper\Data $pricingHelper,
		\Magento\Store\Model\StoreFactory $storeFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\MW\RewardPoints\Model\ProductpointFactory $productpointFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
		\MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
		\MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory,
		\MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory,
		\MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
		\Magento\Framework\Session\SessionManagerInterface $sessionManager,
		\Magento\Checkout\Model\Cart $checkoutCart,
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		\Magento\Framework\View\Element\Template $block
	) {
		parent::__construct($context);
		$this->_reinitConfig = $reinitConfig;
		$this->_moduleList = $moduleList;
		$this->_config = $config;
		$this->_scopeConfig = $scopeConfig;
		$this->_defaultCustomerFactory = $defaultCustomerFactory;
		$this->_customerFactory = $customerFactory;
		$this->_type = $type;
		$this->_dateTime = $dateTime;
		$this->_localeDate = $localeDate;
		$this->_transportBuilder = $transportBuilder;
		$this->_pricingHelper = $pricingHelper;
		$this->_storeFactory = $storeFactory;
		$this->_storeManager = $storeManager;
		$this->_productpointFactory = $productpointFactory;
		$this->_customerSession = $customerSession;
		$this->_checkoutSession = $checkoutSession;
		$this->_inlineTranslation = $inlineTranslation;
		$this->_messageManager = $messageManager;
		$this->_activerulesFactory = $activerulesFactory;
		$this->_catalogrulesFactory = $catalogrulesFactory;
		$this->_cartrulesFactory = $cartrulesFactory;
		$this->_productsellpointFactory = $productsellpointFactory;
		$this->_historyFactory = $historyFactory;
		$this->_sessionManager = $sessionManager;
		$this->_checkoutCart = $checkoutCart;
		$this->_priceCurrency = $priceCurrency;
		$this->_block = $block;
	}

	/**
	 * Retrieve store config value
	 *
	 * @param $xmlPath
	 * @param null $storeCode
	 * @return mixed
	 */
	public function getStoreConfig($xmlPath, $storeCode = null)
	{
		if ($storeCode != null) {
			return $this->_scopeConfig->getValue(
				$xmlPath,
				ScopeInterface::SCOPE_STORE,
				$storeCode
			);
		} else {
			return $this->_scopeConfig->getValue(
				$xmlPath,
				ScopeInterface::SCOPE_STORE
			);
		}
	}

	/**
	 * Check module is enabled or not
	 *
	 * @return int
	 */
	public function moduleEnabled()
	{
		return (int) $this->getStoreConfig(self::MYCONFIG);
	}

	/**
	 * Disable module
	 *
	 * @return void
	 */
	public function disableConfig()
	{
		$this->_config->saveConfig(
			self::MYCONFIG,
			0,
			ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
			0
		);

		$this->_config->saveConfig(
			'advanced/modules_disable_output/'.self::MYNAME,
			1,
			ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
			0
		);

		$this->_reinitConfig->reinit();
	}

	/**
	 * Get customer edit link
	 *
	 * @param  int $customerId
	 * @param  string $detail
	 * @return string
	 */
	public function getLinkCustomer($customerId, $detail)
	{
		return '<b><a href="'.$this->_urlBuilder->getUrl('customer/index/edit', ['id' => $customerId]).'">'.__($detail).'</a></b>';
	}

	/**
	 * Check Store Credit module is enabled or not
	 *
	 * @return bool
	 */
	public function getCreditModule()
	{
		// Get name of all modules
		$modules = $this->_moduleList->getNames();
		// Check MW_Credit module is available or not
		if (in_array('MW_Credit', $modules)) {
			if ((int) $this->getStoreConfig('credit/general/enabled')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check and insert new member
	 *
	 * @param  int $customerId
	 * @param  int $friendId
	 * @return void
	 */
	public function checkAndInsertCustomerId($customerId, $friendId)
	{
		if ($customerId) {
			$customerModel = $this->_customerFactory->create();
			$customerCollection = $customerModel->getCollection()
				->addFieldToFilter('customer_id', $customerId);

			if (sizeof($customerCollection) == 0) {
				$memberData = [
					'customer_id' => $customerId,
					'mw_friend_id' => (($friendId) ? $friendId : 0)
				];
				$customerModel->getResource()->insertNewMember($memberData);
			}
		}
	}

	/**
	 * Get Store by Store ID
	 *
	 * @param  int $storeId
	 * @return \Magento\Store\Model\Store
	 */
	public function getStoreById($storeId)
	{
		return $this->_storeFactory->create()->load($storeId);
	}

	/******************************** Get configurations via backend ********************************/
	public function allowSendEmailNotifications($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/email_notifications/enable_notifications', $storeCode);
	}

	public function getFacebookLikeAppId()
	{
		return $this->getStoreConfig('rewardpoints/facebook/appid');
	}

	public function getPointMoneyRateConfig($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/general/point_money_rate', $storeCode);
	}

	public function pointCreditRate()
	{
		return $this->getStoreConfig('rewardpoints/using_points/point_credit_rate');
	}

	public function getEnablePointCurrencyImage($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/enable_image', $storeCode);
	}

	public function getPointCurency($storeCode = null)
	{
		return trim($this->getStoreConfig('rewardpoints/display/point_curency', $storeCode));
	}

	public function getPointCurrencyImage($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/point_curency_image', $storeCode);
	}

	public function allowExchangePointToCredit()
	{
		return $this->getStoreConfig('rewardpoints/using_points/enabled');
	}

	public function getRewardIcon($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/reward_icon', $storeCode);
	}

	public function getLinkRewardIconTo($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/link_reward_icon_to', $storeCode);
	}

	public function allowSendRewardPointsToFriend()
	{
		return $this->getStoreConfig('rewardpoints/using_points/allow_send_reward_point_to_friend');
	}

	public function getPointStepConfig($storeCode = null)
	{
		return (int) $this->getStoreConfig('rewardpoints/general/point_step', $storeCode);
	}

	public function getStatusAddRewardPointStore($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/general/status_add_reward_point', $storeCode);
	}

	public function getSubtractPointWhenRefundConfigStore($storeCode)
	{
		return $this->getStoreConfig('rewardpoints/general/subtract_reward_point',$storeCode);
	}

	public function getRestoreSpentPointsWhenRefundConfigStore($storeCode)
	{
		return $this->getStoreConfig('rewardpoints/general/restore_spent_points',$storeCode);
	}

	public function timeLifeSendRewardPointsToFriend($storeCode)
	{
		return $this->getStoreConfig('rewardpoints/using_points/time_life', $storeCode);
	}

	public function getApplyRewardPoints($storeCode)
	{
		return $this->getStoreConfig('rewardpoints/general/apply_reward_point', $storeCode);
	}

	public function getApplyRewardPointsTax($storeCode)
	{
		return $this->getStoreConfig('rewardpoints/general/apply_reward_point_tax', $storeCode);
	}

	public function getCouponRwpConfig($storeCode)
	{
		return (int) $this->getStoreConfig('rewardpoints/general/using_coupon_rwp', $storeCode);
	}

	public function getRedeemPointsOnTax($storeCode)
	{
		return $this->getStoreConfig('rewardpoints/general/redeem_point_on_tax', $storeCode);
	}

	public function getRedeemedTaxConfig($storeCode)
	{
		return (int) $this->getStoreConfig('rewardpoints/general/redeemed_tax', $storeCode);
	}

	public function getRedeemedShippingConfig($storeCode)
	{
		return (int) $this->getStoreConfig('rewardpoints/general/redeemed_shipping', $storeCode);
	}

	public function getEnablePromotionMessage($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/enable_message', $storeCode);
	}

	public function getEnablePromotionBanner($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/enable_banner', $storeCode);
	}

	public function getPromtionBannerSize($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/banner_size', $storeCode);
	}

	public function getEnableShowCreditInfo($storeCode = null)
	{
		return $this->getStoreConfig('rewardpoints/display/show_credit_info', $storeCode);
	}
	
	public function getDefaultCommentConfig($storeCode = null)
    {
        return $this->getStoreConfig('rewardpoints/config/default_comment', $storeCode);
    }
	/******************************** End configurations via backend ********************************/

	/**
	 * Get expiration days
	 *
	 * @param  string $storeCode
	 * @return int
	 */
	public function getExpirationDaysPoint($storeCode)
	{
		$expiredDay = 0;
		$config = $this->getStoreConfig('rewardpoints/general/expiration_days', $storeCode);

		if ($config != '') {
			$expiredDay = (int) $config;
		}

		return $expiredDay;
	}

	/**
	 * @param $earnRewardpoint
	 * @param $expiredDay
	 * @return array
	 */
	public function getTransactionByExpiredDayAndPoints($earnRewardpoint, $expiredDay)
	{
		$expiredTime    = null;
		$remainingPoints = 0;
		if ($expiredDay > 0) {
			$expiredTime    = time() + $expiredDay * 24 * 3600;
			$remainingPoints = $earnRewardpoint;
		}

		$results = [
			$expiredTime,$remainingPoints];

		return $results;
	}

	/**
	 * Format money to current currence
	 *
	 * @param  decimal $money
	 * @param  boolean $format
	 * @param  boolean $includeContainer
	 * @return decimal
	 */
	public function formatMoney($money, $format = true, $includeContainer = true)
	{
		return $this->_pricingHelper->currency($money, $format, $includeContainer);
	}

	/**
	 * Exchange points to money
	 *
	 * @param $rewardpoints
	 * @param null $storeCode
	 * @return float
	 */
	public function exchangePointsToMoneys($rewardpoints, $storeCode = null)
	{
		$rate = $this->getPointMoneyRateConfig($storeCode);
		$rate = explode('/', $rate);
		$money = ($rewardpoints * 1.0 * $rate[1]) / $rate[0];

		return round($money, 2);
	}

	/**
	 * Exchange money to points
	 *
	 * @param $money
	 * @param null $storeCode
	 * @return float
	 */
	public function exchangeMoneysToPoints($money, $storeCode = null)
	{
		$rate = $this->getPointMoneyRateConfig($storeCode);
		$rate = explode('/', $rate);
		$points = ($money * 1.0 * $rate[0]) / $rate[1];

		return ceil($points);
	}

	/**
	 * Retrive rounded points value
	 *
	 * @param $points
	 * @param null $storeCode
	 * @param bool|true $up
	 * @return float|int
	 */
	public function roundPoints($points, $storeCode = null, $up = true)
	{
		$step = $this->getPointStepConfig($storeCode);
		$tmp = (int) ($points / $step) * $step;

		if ($up) {
			return round(($points / $step), 0) * $step;
		}

		return $tmp;
	}

	/**
	 * Retrive reward points text with icon or not
	 *
	 * @param int $points
	 * @param null $storeCode
	 * @return string
	 */
	public function formatPoints($points, $storeCode = null)
	{
		$position = $this->getStoreConfig('rewardpoints/display/curency_position', $storeCode);
		$_points = number_format($points, 0, '.', ',');
		$enableCurencyImage = (int) $this->getEnablePointCurrencyImage($storeCode);

		$money = '';
		if ($this->getEnableShowCreditInfo($storeCode)) {
			$money = ' (' . (string) $this->formatMoney($this->exchangePointsToMoneys($points, $storeCode)) . ')';
		}
		if ($enableCurencyImage) {
			$pointImage = $this->getPointCurrencyImage($storeCode);
			$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(DirectoryList::MEDIA);
			if ($pointImage == '') {
				$pointImageUrl = $this->_block->getViewFileUrl('MW_RewardPoints::images/default/mw_money.png');
			} else {
				$pointImageUrl = $mediaUrl.'mw_rewardpoint/'.$pointImage;
			}

			if ($position == Position::BEFORE) {
				return '<span class="mw_rewardpoints"><img src="'.$pointImageUrl.'" alt="Reward points" style="vertical-align: middle"> '." ".$_points.$money.'</span>';
			}

			return '<span class="mw_rewardpoints">'.$_points." ".'<img src="'.$pointImageUrl.'" alt="Reward points" style="vertical-align: middle">'.$money.'</span>';
		}

		if ($position == Position::BEFORE) {
			return '<span class="mw_rewardpoints">'.$this->getPointCurency($storeCode)." ".$_points.$money.'</span>';
		}

		return '<span class="mw_rewardpoints">'.$_points." ".$this->getPointCurency($storeCode).$money.'</span>';
	}

	/**
	 * Retrive reward points icon
	 *
	 * @param null $storeCode
	 * @return string
	 */
	public function getRewardIconHtml($storeCode = null)
	{
		$imageRewardIcon = $this->getRewardIcon($storeCode);
		if ($imageRewardIcon == '') {
			$imageRewardIconUrl = $this->_block->getViewFileUrl('MW_RewardPoints::images/default/reward_icon.gif');
		} else {
			$imageRewardIconUrl = $mediaUrl.'mw_rewardpoint/'.$imageRewardIcon;
		}

		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(DirectoryList::MEDIA);
		$rewardIcon = '<a style="margin-right: 5px;" href="'.$this->getLinkRewardIconTo($storeCode).'" target="_blank">
							<span><img style ="vertical-align: middle;" alt="Reward Points Policy" src="'.$imageRewardIconUrl.'"></span>
						</a>';

		return $rewardIcon;
	}

	/**
	 * Retrive customer invitation link
	 *
	 * @param  \Magento\Customer\Model\Customer $customer
	 * @return string
	 */
	public function getLink(\Magento\Customer\Model\Customer $customer)
	{
		return $this->_storeManager->getStore()->getBaseUrl()."?mw_reward=".md5($customer->getEmail());
	}

	/**
	 * Retrive customer ajax invitation link
	 *
	 * @param  \Magento\Customer\Model\Customer $customer
	 * @param  string $link
	 * @return string
	 */
	public function getLinkAjax(\Magento\Customer\Model\Customer $customer, $link)
	{
		return trim($link)."?mw_reward=".md5($customer->getEmail());
	}

	/**
	 * Retrive checkout Reward Points rule
	 *
	 * @param  \Magento\Quote\Model\Quote $quote
	 * @param  string $storeCode
	 * @return array
	 */
	public function getCheckoutRewardPointsRule($quote, $storeCode = null)
	{
		if (is_null($storeCode)) {
			$storeCode = $this->_storeManager->getStore()->getCode();
		}

		$rules = [];
		$pointDetails = unserialize($quote->getMwRewardpointDetail());
		if ($pointDetails) {
			foreach ($pointDetails as $key => $pointDetail) {
				if ($pointDetail > 0) {
					$detail  = trim($this->_cartrulesFactory->create()->load($key)->getDescription());
					$rules[] = [
						'message' => __('%1 (%2)', $this->formatPoints($pointDetail, $storeCode), $detail),
						'amount' => $pointDetail,
						'qty' => 1
					];
				}
			}
		}

		foreach ($quote->getAllItems() as $item) {
			if ($item->getParentItemId() == null) {
				$productId = $item->getProduct()->getId();
				$product = $item->getProduct()->load($productId);
				$mwRewardPoint = (int) $this->_catalogrulesFactory->create()->getPointCatalogRule($productId);

				if ($info = $item->getProduct()->getCustomOption('info_buyRequest')) {
					$infoArr = unserialize($info->getValue());
					if ($infoArr) {
						if (isset($infoArr['super_attribute'])) {
							$model = $this->_productsellpointFactory->create();
							foreach ($infoArr['super_attribute'] as $attributeId => $value) {
								$collection = $model->getCollection()
									->addFieldToFilter('product_id', $product->getId())
									->addFieldToFilter('option_id', $value)
									->addFieldToFilter('option_type_id', $attributeId)
									->addFieldToFilter('type_id', 'super_attribute')
									->getFirstItem();
								$mwRewardPoint = $mwRewardPoint + intval($collection->getEarnPoint());
							}
						}
					}
				}

				if ($product->getTypeId() == 'bundle') {
					$mwRewardPoint = 0;
					foreach ($item->getChildren() as $child) {
						$mwRewardPoint += $child->getQty() * $this->_catalogrulesFactory->create()->getPointCatalogRule($child->getProductId());
					}
				}

				if ($mwRewardPoint > 0) {
					$rules[] = [
						'message' => __(
							'%1 for product: <b>%2</b>',
							$this->formatPoints($mwRewardPoint, $storeCode),
							$product->getName()
						),
						'amount' => $mwRewardPoint,
						'qty' => $item->getQty()
					];
				}
			}
		}

		if ($quote->getCustomerId()) {
			if (!$this->checkCustomerMaxBalance($quote->getCustomerId(), $storeCode)) {
				$rules = [];
			}
		}

		return $rules;
	}

	/**
	 * @param null $quote
	 * @param null $customerId
	 * @param null $storeCode
	 * @param null $baseGrandTotal
	 * @return int
	 */
	public function getMaxPointToCheckOut($quote = null, $customerId = null, $storeCode = null, $baseGrandTotal = null)
	{
		if (is_null($storeCode)) {
			$storeCode = $this->_storeManager->getStore()->getId();
		}

		if (is_null($customerId)) {
			$customerId = $this->_customerSession->getCustomer()->getId();
		}

		if (is_null($quote)) {
			$quote = $this->_checkoutSession->getQuote();
		}

		if (is_null($baseGrandTotal)) {
			$baseGrandTotal = $quote->getBaseGrandTotal();
		}

		$points = $this->exchangeMoneysToPoints($baseGrandTotal + $quote->getMwRewardpointDiscount(), $storeCode);
		$customerPoints = $this->_customerFactory->create()->load($customerId)->getMwRewardPoint();
		$maxPoints = $quote->getSpendRewardpointCart();
		if ($maxPoints) {
			if ($customerPoints >= $maxPoints) {
				if ($maxPoints < $points) {
					return $this->roundPoints($maxPoints, $storeCode, true);
				}

				return $this->roundPoints($points, $storeCode, true);
			} else {
				if ($customerPoints < $points) {
					return $this->roundPoints($customerPoints, $storeCode, false);
				}

				return $this->roundPoints($points, $storeCode, false);
			}
		}

		return 0;
	}

	/**
	 * Retrive minimum points condition to checkout
	 *
	 * @param null $storeCode
	 * @return int
	 */
	public function getMinPointCheckoutStore($storeCode = null)
	{
		$min = (int) $this->getStoreConfig('rewardpoints/general/min_checkout', $storeCode);
		if ($min == '') {
			$min = 0;
		}

		return $min;
	}

	/**
	 * Retrive maximum points condition to checkout
	 *
	 * @param null $storeCode
	 * @return int
	 */
	public function getMaxPointBlanceStore($storeCode = null)
	{
		$max = (int) $this->getStoreConfig('rewardpoints/general/max_point_balance', $storeCode);
		if ($max == '') {
			$max = 0;
		}

		return $max;
	}

	/**
	 * Check points of customer are greater than his blance or not
	 *
	 * @param  int  	$customerId
	 * @param  string  	$storeCode
	 * @param  int 		$pointAdd
	 * @return bool
	 */
	public function checkCustomerMaxBalance($customerId, $storeCode, $pointAdd = 0)
	{
		if ($customerId == 0) {
			return false;
		}

		$max = (int) $this->getMaxPointBlanceStore($storeCode);
		if ($max) {
			$point = (int) $this->_customerFactory->create()->load($customerId)->getMwRewardPoint();
			if (($point + $pointAdd) >= $max) {
				return false;
			}
		};

		return true;
	}

	/**
	 * Set Redeem Points to checkout
	 *
	 * @param $rewardpoints
	 * @param null $quote
	 * @param null $customerId
	 * @param null $storeCode
	 * @param null $baseGrandTotal
	 */
	public function setPointToCheckOut($rewardpoints, $quote = null, $customerId = null, $storeCode = null, $baseGrandTotal = null)
	{
		if (is_null($storeCode)) {
			$storeCode = $this->_storeManager->getStore()->getCode();
		}

		if (is_null($customerId)) {
			$customerId = $this->_customerSession->getCustomer()->getId();
		}

		if (is_null($quote)) {
			$quote = $this->_checkoutSession->getQuote();
		}

		if ($customerId) {
			$_customer = $this->_customerFactory->create()->load($customerId);

			if (is_null($baseGrandTotal)) {
				$baseGrandTotal = $quote->getBaseGrandTotal();
			}

			$maxPoints = $this->getMaxPointToCheckOut($quote, $customerId, $storeCode, $baseGrandTotal);
			$rewardpointDiscount = (double) $quote->getMwRewardpointDiscount();
			$subtotalAfterRewardpoint = $baseGrandTotal + $rewardpointDiscount;
			$points = $this->exchangeMoneysToPoints($subtotalAfterRewardpoint, $storeCode);
			$customerPoints = $_customer->getMwRewardPoint();

			if ($maxPoints) {
				$tmp = $this->roundPoints($maxPoints, $storeCode);
			} else {
				$tmp = $this->roundPoints($points, $storeCode);
			}

			if ($rewardpoints <= $tmp && $rewardpoints <= $customerPoints) {
				$minPoints = (float) $this->getStoreConfig('rewardpoints/general/min_checkout');

				if ($rewardpoints >= $minPoints) {
					$money = $this->exchangePointsToMoneys($rewardpoints, $storeCode);

					if ($money > $subtotalAfterRewardpoint) {
						$money        = $subtotalAfterRewardpoint;
						$rewardpoints = $this->exchangeMoneysToPoints($money, $storeCode);
						$rewardpoints = $this->roundPoints($rewardpoints, $storeCode);
					}

					$money = round($money, 2);
					if ($money == 0) {
						$rewardpointDiscountShow = 0;
					} else {
						$rewardpointDiscountShow = $this->_priceCurrency->convert($money);
					}
					$quote->setMwRewardpoint($rewardpoints)
						->setMwRewardpointDiscount($money)
						->setMwRewardpointDiscountShow($rewardpointDiscountShow)
						->save();
					$this->_sessionManager->setMwRewardpointAfterDrop($rewardpoints);
				} else {
					$quote->setMwRewardpoint(0)
						->setMwRewardpointDiscount(0)
						->setMwRewardpointDiscountShow(0)
						->save();
					$this->_sessionManager->setMwRewardpointAfterDrop(0);
				}
			}
		}
	}

	/**
	 * Check One Step Checkout module is enabled or not
	 *
	 * @return bool
	 */
	public function isOSCRunning()
	{
		if ($this->isModuleOutputEnabled('MW_Onestepcheckout')) {
			if ($this->getStoreConfig('onestepcheckout/general/enabled')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get link of custom rule
	 *
	 * @param  int $ruleId
	 * @return string
	 */
	public function getLinkCustomRule($ruleId = null)
	{
		if ($ruleId == null) {
			return '';
		} else {
			$type = $this->_activerulesFactory->create()->load($ruleId)->getTypeOfTransaction();
			if ($type == Type::CUSTOM_RULE) {
				$data = 'abc,' . trim($ruleId);
				$dataEncrypt  = base64_encode($data);

				return $this->_urlBuilder->getBaseUrl() . '?mw_rule=' . $dataEncrypt;
			} else {
				return '';
			}
		}
	}

	/**
	 * @param $earnRewardPoint
	 * @param $storeCode
	 * @return array
	 */
	public function getTransactionExpiredPoints($earnRewardPoint, $storeCode)
	{
		$expiredTime     = null;
		$remainingPoints = 0;
		$expiredDay      = (int) $this->getExpirationDaysPoint($storeCode);
		if ($expiredDay > 0) {
			$expiredTime     = time() + $expiredDay * 24 * 3600;
			$remainingPoints = $earnRewardPoint;
		}

		$results = [
			$expiredDay,
			$expiredTime,
			$remainingPoints
		];

		return $results;
	}

	/**
	 * @param $customerId
	 * @param $points
	 */
	public function processExpiredPointsWhenSpentPoints($customerId, $points)
	{
		$collection = $this->_historyFactory->create()->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToFilter('type_of_transaction', ['in' => Type::getAddPointArray()])
			->addFieldToFilter('status', Status::COMPLETE)
			->addFieldToFilter('expired_time', ['neq' => null])
			->addFieldToFilter('point_remaining', ['gt' => 0])
			->setOrder('expired_time', 'DESC')
			->setOrder('history_id', 'ASC');

		foreach ($collection as $transaction) {
			$remainingPoints = (int)$transaction->getPointRemaining();
			if ($remainingPoints >= $points) {
				$transaction->setPointRemaining($remainingPoints - $points)->save();
				break;
			} else if ($remainingPoints < $points) {
				$transaction->setPointRemaining(0)->save();
				$points = $points - $remainingPoints;
			}
		}
	}

	/**
	 * @param $oldLink
	 * @return mixed
	 */
	public function getLinkShareFacebook($oldLink)
	{
		$data1 = explode('www.',$oldLink);
		$data2 = explode('://',$oldLink);

		if (isset($data1[1]) && $data1[1] != null) {
			$newLink = $data1[1];
		} else if (isset($data2[1]) && $data2[1] != null) {
			$newLink = $data2[1];
		} else {
			$newLink = $oldLink;
		}

		return $newLink;
	}

	/**
	 * @return int
	 */
	public function getCustomerGroupIdFrontend()
	{
		$groupId = 0;
		$login = $this->_customerSession->isLoggedIn();
		if ($login) {
			$groupId = (int) $this->_customerSession->getCustomerGroupId();
		}

		return $groupId;
	}

	/**
	 * @return float
	 */
	public function _calOffsetHourGMT()
	{
		$offset = round(
			$this->_dateTime->calculateOffset(
				$this->_storeManager->getStore()->getConfig('general/locale/timezone')
			)/60/60
		);

		if ($offset >= 0) {
			$offset = '+' . $offset;
		}

		return $offset;
	}

	/**
	 * Clear reward points information
	 */
	public function setEmptyRewardpoint()
	{
		$quote = $this->_checkoutCart->getQuote();
		if (count($quote->getAllItems()) == 0) {
			$this->_sessionManager->setMwRewardpointDiscountShowTotal(0);
			$this->_sessionManager->setMwRewardpointDiscountTotal(0);
			$this->_sessionManager->setMwRewardpointAfterDrop(0);

			$address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
			$address->setMwRewardpoint(0);
			$address->setMwRewardpointDiscountShow(0);
			$address->setMwRewardpointDiscount(0);

			$quote->setMwRewardpoint(0);
			$quote->setMwRewardpointDiscountShow(0);
			$quote->setSpendRewardpointCart(0);
			$quote->save();
		}
	}

	/**
	 * @param null $storeCode
	 * @return int
	 */
	public function getExpirationDaysEmail($storeCode = null)
	{
		$expirationDaysConfig = $this->getStoreConfig('rewardpoints/email_notifications/expiration_days', $storeCode);
		if ($expirationDaysConfig == '') {
			$expiredDay = 0;
		} else {
			$expiredDay = (int) $expirationDaysConfig;
		}

		return $expiredDay;
	}

	/**
	 * Send email to customer when point is changed
	 *
	 * @param  int 	  $customerId
	 * @param  array  $data
	 * @param  string $storeCode
	 * @return void
	 */
	public function sendEmailCustomerPointChanged($customerId, $data, $storeCode)
	{
		$subscribedBalanceUpdate = $this->_customerFactory->create()
			->load($customerId)
			->getSubscribedBalanceUpdate();

		if ($this->allowSendEmailNotifications($storeCode) && $subscribedBalanceUpdate == 1) {
			$storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
			$sender = $this->getStoreConfig('rewardpoints/email_notifications/email_sender', $storeCode);
			$customer = $this->_defaultCustomerFactory->create()->load($customerId);
			$emailTemplate = 'rewardpoints/email_notifications/points_balance';
			$senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
			$customerLink = $this->_storeFactory->create()->load($storeCode, 'code')
				->getUrl('rewardpoints/rewardpoints/index');
			$comment = $this->_type->getTransactionDetail(
				$data['type_of_transaction'],
				$data['transaction_detail'],
				$data['status']
			);
			$transactionAmount = $this->_type->getAmountWithSign(
				$data['amount'],
				$data['type_of_transaction']
			);

			$emailData = [
				'customer_name' => $customer->getName(),
				'transaction_amount' => $transactionAmount,
				'customer_balance' => $data['balance'],
				'transaction_detail' => $comment,
				'transaction_time' => $this->_localeDate->formatDateTime(
					new \DateTime($data['transaction_time']),
					\IntlDateFormatter::MEDIUM,
					\IntlDateFormatter::MEDIUM
				),
				'sender_name' => $senderName,
				'store_name' => $storeName,
				'customer_link' => $customerLink
			];

			// Send notification email
			$this->_sendEmailTransaction(
				$sender,
				$customer->getEmail(),
				$customer->getName(),
				$emailTemplate,
				$emailData,
				$storeCode
			);
		}
	}

	/**
	 * Send email to customer (Referral) when point is changed
	 *
	 * @param  int    $customerId
	 * @param  array  $data
	 * @param  string $storeCode
	 * @return void
	 */
	public function sendEmailCustomerPointChangedNew($customerId, $data, $storeCode)
	{
		$subscribedBalanceUpdate = $this->_customerFactory->create()
			->load($customerId)
			->getSubscribedBalanceUpdate();

		if ($this->allowSendEmailNotifications($storeCode) && $subscribedBalanceUpdate == 1) {
			$storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
			$sender = $this->getStoreConfig('rewardpoints/email_notifications/email_sender', $storeCode);
			$customer = $this->_defaultCustomerFactory->create()->load($customerId);
			$emailTemplate = 'rewardpoints/email_notifications/points_balance';
			$senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
			$customerLink = $this->_urlBuilder->getBaseUrl();
			$comment = $this->_type->getTransactionDetail(
				$data['type_of_transaction'],
				$data['transaction_detail'],
				$data['status']
			);
			$transactionAmount = $this->_type->getAmountWithSign(
				$data['amount'],
				$data['type_of_transaction']
			);

			$emailData = [
				'customer_name' => $customer->getName(),
				'transaction_amount' => $transactionAmount,
				'customer_balance' => $data['balance'],
				'transaction_detail' => $comment,
				'transaction_time' => $this->_localeDate->formatDateTime(
					new \DateTime($data['transaction_time']),
					\IntlDateFormatter::MEDIUM,
					\IntlDateFormatter::MEDIUM
				),
				'sender_name' => $senderName,
				'store_name' => $storeName,
				'customer_link' => $customerLink
			];

			// Send notification email
			$this->_sendEmailTransaction(
				$sender,
				$customer->getEmail(),
				$customer->getName(),
				$emailTemplate,
				$emailData,
				$storeCode
			);
		}
	}

	/**
	 * Send expiration points email to customer
	 *
	 * @param $customerId
	 * @param $data
	 * @param $storeCode
	 */
	public function sendEmailCustomerPointExpiration($customerId, $data, $storeCode)
	{
		if ($this->allowSendEmailNotifications($storeCode)) {
			$storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
			$sender = $this->getStoreConfig('rewardpoints/email_notifications/email_sender', $storeCode);
			$customer = $this->_defaultCustomerFactory->create()->load($customerId);
			$emailTemplate = 'rewardpoints/email_notifications/points_expiration';
			$senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
			$customerLink = $this->_storeFactory->create()->load($storeCode, 'code')
				->getUrl('rewardpoints/rewardpoints/index');

			$emailData = [
				'customer_name' => $customer->getName(),
				'transaction_amount' => $data['amount'],
				'customer_balance' => $data['balance'],
				'transaction_time' => $this->_localeDate->formatDateTime(
					new \DateTime($data['transaction_time']),
					\IntlDateFormatter::MEDIUM,
					\IntlDateFormatter::MEDIUM
				),
				'sender_name' => $senderName,
				'store_name' => $storeName,
				'customer_link' => $customerLink,
				'email_contact' => $this->getStoreConfig('trans_email/ident_support/email', $storeCode),
				'phone_contact' => $this->getStoreConfig('general/store_information/phone', $storeCode)
			];

			// Send notification email
			$this->_sendEmailTransaction(
				$sender,
				$customer->getEmail(),
				$customer->getName(),
				$emailTemplate,
				$emailData,
				$storeCode
			);
		}
	}

	/**
	 * Send birthday points email to customer
	 *
	 * @param  int    $customerId
	 * @param  array  $data
	 * @param  string $storeCode
	 * @return void
	 */
	public function sendEmailCustomerPointBirthday($customerId, $data, $storeCode)
	{
		if ($this->allowSendEmailNotifications($storeCode)) {
			$storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
			$sender = $this->getStoreConfig('rewardpoints/email_notifications/email_sender', $storeCode);
			$customer = $this->_defaultCustomerFactory->create()->load($customerId);
			$emailTemplate = 'rewardpoints/email_notifications/points_birthday';
			$senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
			$customerLink = $this->_storeFactory->create()->load($storeCode, 'code')
				->getUrl('rewardpoints/rewardpoints/index');
			$comment = $this->_type->getTransactionDetail(
				$data['type_of_transaction'],
				$data['transaction_detail'],
				$data['status']
			);
			$transactionAmount = $this->_type->getAmountWithSign(
				$data['amount'],
				$data['type_of_transaction']
			);

			$emailData = [
				'customer_name' => $customer->getName(),
				'transaction_amount' => $transactionAmount,
				'customer_balance' => $data['balance'],
				'transaction_detail' => $comment,
				'transaction_time' => $this->_localeDate->formatDateTime(
					new \DateTime($data['transaction_time']),
					\IntlDateFormatter::MEDIUM,
					\IntlDateFormatter::MEDIUM
				),
				'sender_name' => $senderName,
				'store_name' => $storeName,
				'customer_link' => $customerLink
			];

			// Send notification email
			$this->_sendEmailTransaction(
				$sender,
				$customer->getEmail(),
				$customer->getName(),
				$emailTemplate,
				$emailData,
				$storeCode
			);
		}
	}

	/**
	 * Send notification emails
	 *
	 * @param  string $sender
	 * @param  string $emailTo
	 * @param  string $name
	 * @param  string $template
	 * @param  array  $data
	 * @param  string $storeCode
	 * @return void
	 */
	public function _sendEmailTransaction($sender, $emailTo, $name, $template, $data, $storeCode)
	{
		$data['subject'] = __('Reward Points System!');
		$templateId = $this->getStoreConfig($template, $storeCode);
		$storeId = $this->_storeFactory->create()->load($storeCode, 'code')->getId();

		try {
			$this->_inlineTranslation->suspend();
			$this->_transportBuilder->setTemplateIdentifier(
				$templateId
			)->setTemplateOptions(
				[
					'area' => Area::AREA_FRONTEND,
					'store' => $storeId
				]
			)->setTemplateVars(
				$data
			)->setFrom(
				[
					'email' => $sender,
					'name' => $data['sender_name']
				]
			)->addTo(
				$emailTo,
				$name
			);
			$transport = $this->_transportBuilder->getTransport();
			$transport->sendMessage();
			$this->_inlineTranslation->resume();
		} catch (\Exception $e) {
			$this->_messageManager->addError(__("Email can not send !"));
		}
	}
}
