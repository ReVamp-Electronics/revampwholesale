<?php

namespace MW\RewardPoints\Helper;

use MW\RewardPoints\Model\Type;

class Theme extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var \Magento\Framework\View\LayoutFactory
	 */
	protected $_layoutFactory;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @var \MW\RewardPoints\Model\ProductpointFactory
	 */
	protected $_productpointFactory;

	/**
	 * @var \MW\RewardPoints\Model\ActiverulesFactory
	 */
	protected $_activerulesFactory;

	/**
	 * @var \MW\RewardPoints\Model\CatalogrulesFactory
	 */
	protected $_catalogrulesFactory;

	/**
	 * @var \MW\RewardPoints\Model\ProductsellpointFactory
	 */
	protected $_productsellpointFactory;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @var \Magento\Catalog\Model\ProductFactory
	 */
	protected $_productFactory;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\View\LayoutFactory $layoutFactory
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param \MW\RewardPoints\Model\CustomerFactory $customerFactory
	 * @param \MW\RewardPoints\Model\ProductpointFactory $productpointFactory
	 * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
	 * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
	 * @param \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Catalog\Model\ProductFactory $productFactory
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\View\LayoutFactory $layoutFactory,
		\Magento\Customer\Model\Session $customerSession,
		\MW\RewardPoints\Helper\Data $dataHelper,
		\MW\RewardPoints\Model\CustomerFactory $customerFactory,
		\MW\RewardPoints\Model\ProductpointFactory $productpointFactory,
		\MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
		\MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
		\MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Catalog\Model\ProductFactory $productFactory
	) {
		parent::__construct($context);
		$this->_layoutFactory = $layoutFactory;
		$this->_customerSession = $customerSession;
		$this->_dataHelper = $dataHelper;
		$this->_customerFactory = $customerFactory;
		$this->_productpointFactory = $productpointFactory;
		$this->_activerulesFactory = $activerulesFactory;
		$this->_catalogrulesFactory = $catalogrulesFactory;
		$this->_productsellpointFactory = $productsellpointFactory;
		$this->_storeManager = $storeManager;
		$this->_checkoutSession = $checkoutSession;
		$this->_productFactory = $productFactory;
	}

	/**
	 * Display Reward Points in Shopping cart page
	 *
	 * @return html
	 */
	public function getRewardpointCartTemplate()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			return $this->_layoutFactory->create()->createBlock(
				'MW\RewardPoints\Block\Checkout\Cart\Rewardpoints'
			)->setTemplate(
				'MW_RewardPoints::checkout/cart/rewardpoints.phtml'
			)->toHtml();
		}

		return '';
	}

	/**
	 * Display earned points on the checkout page (at the order review step)
	 *
	 * @return html
	 */
	public function getRewardpointOnepageReviewTemplate()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			return $this->_layoutFactory->create()->createBlock(
				'MW\RewardPoints\Block\Checkout\Onepage\Review\Totals\Rewardpoints'
			)->setTemplate(
				'MW_RewardPoints::checkout/onepage/review/totals/rewardpoints.phtml'
			)->toHtml();
		}

		return '';
	}

	/**
	 * Display redeemed points on the checkout page (at the order review step)
	 *
	 * @return html
	 */
	public function getRedeemPointOnepageReviewTemplate()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			return $this->_layoutFactory->create()->createBlock(
				'MW\RewardPoints\Block\Checkout\Onepage\Review\Totals\Rewardpoints'
			)->setTemplate(
				'MW_RewardPoints::checkout/onepage/review/totals/redeempoints.phtml'
			)->toHtml();
		}

		return '';
	}

	/**
	 * Display total points on the checkout page (at the order review step)
	 *
	 * @return html
	 */
	public function getTotalSpentPointOnepageReviewTemplate()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			return $this->_layoutFactory->create()->createBlock(
				'MW\RewardPoints\Block\Checkout\Onepage\Review\Totals\Rewardpoints'
			)->setTemplate(
				'MW_RewardPoints::checkout/onepage/review/totals/spentpoints.phtml'
			)->toHtml();
		}

		return '';
	}

	/**
	 * Display points in Create an account page
	 *
	 * @return html
	 */
	public function getDisplayEarnpointCreateAccount()
	{
		$customerGroupId = $this->_dataHelper->getCustomerGroupIdFrontend();
		$mwRewardPoint   = (int) $this->_activerulesFactory->create()->getPointActiveRules(
			Type::REGISTERING,
			$customerGroupId
		);

		if ($mwRewardPoint > 0 && $this->_dataHelper->moduleEnabled()) {
			$rewardIcon = $this->_dataHelper->getRewardIconHtml();
			return '<span class="mw_display_point" style="display: inline-block; margin-bottom: 20px">'
			. $rewardIcon
			. __("Earn <b>%1</b> for creating new account.", $this->_dataHelper->formatPoints($mwRewardPoint))
			. '</span>';
		}

		return '';
	}

	/**
	 * Display points in product reviews page
	 *
	 * @return html
	 */
	public function getDisplayEarnpointSubmitProductReview()
	{
		$customerGroupId = $this->_dataHelper->getCustomerGroupIdFrontend();
		$mwRewardPoint   = (int) $this->_activerulesFactory->create()->getPointActiveRules(
			Type::SUBMIT_PRODUCT_REVIEW,
			$customerGroupId
		);

		if ($mwRewardPoint > 0 && $this->_dataHelper->moduleEnabled()) {
			$rewardIcon = $this->_dataHelper->getRewardIconHtml();
			return '<span class="mw_display_point" style="display: inline-block; margin-bottom: 20px">'
			. $rewardIcon
			. __("Earn <b>%1</b> when you submit product review.", $this->_dataHelper->formatPoints($mwRewardPoint))
			. '</span>';
		}

		return '';
	}

	/**
	 * Display points in Subscribe to the newsletter page
	 *
	 * @return html
	 */
	public function getDisplayEarnpointSignUpNewLetter()
	{
		$customerGroupId = $this->_dataHelper->getCustomerGroupIdFrontend();
		$mwRewardPoint   = (int) $this->_activerulesFactory->create()->getPointActiveRules(
			Type::SIGNING_UP_NEWLETTER,
			$customerGroupId
		);

		if ($mwRewardPoint > 0 && $this->_dataHelper->moduleEnabled()) {
			$rewardIcon = $this->_dataHelper->getRewardIconHtml();
			return '<span class="mw_display_point" style="display: inline-block; margin-bottom: 20px">'
			. $rewardIcon
			. __("Earn <b>%1</b> when you signing up newletter.", $this->_dataHelper->formatPoints($mwRewardPoint))
			. '</span>';
		}

		return '';
	}

	/**
	 * Display points in Category pages
	 *
	 * @param  \Magento\Catalog\Model\Product $_product
	 * @return html
	 */
	public function getDisplayEarnpointListProduct($_product)
	{
		$html = $this->getDisplaySellPoints($_product);
		$mwRewardPoint = $this->_catalogrulesFactory->create()->getPointCatalogRule($_product->getId());
		if ($_product->getTypeId() == 'configurable') {
			$collection = $this->_productsellpointFactory->create()->getCollection()
				->addFieldToFilter('product_id', $_product->getId())
				->addExpressionFieldToSelect('min_earn_point', 'MIN(earn_point)', []);
			$collection->getSelect()->group('option_type_id');

			$totalMinPoint = 0;
			foreach ($collection->getData() as $point) {
				$totalMinPoint += $point['min_earn_point'];
			}

			$mwRewardPoint += $totalMinPoint;
		}

		$rewardIcon = $this->_dataHelper->getRewardIconHtml();
		if ($mwRewardPoint > 0 && $this->_dataHelper->moduleEnabled()) {
			return $html
			. '<span class="mw_display_point" style="display: inline-block; margin-bottom: 20px">'
			. $rewardIcon . __("Earn <b>%1</b>", $this->_dataHelper->formatPoints($mwRewardPoint))
			. '</span>';
		}

		return $html;
	}

	/**
	 * Display points in Products pages
	 *
	 * @param  \Magento\Catalog\Model\Product $_product
	 * @return html
	 */
	public function getDisplayEarnpointViewProduct($_product)
	{
		$html = $this->getDisplaySellPoints($_product);
		$mwRewardPoint = $this->_catalogrulesFactory->create()->getPointCatalogRule($_product->getId());
		$rewardIcon = $this->_dataHelper->getRewardIconHtml();

		if ($mwRewardPoint > 0 && $this->_dataHelper->moduleEnabled()) {
			$hidden = '<input type="hidden" name="mw_inp_earn_point" value="' . $mwRewardPoint . '">';

			return $html
			. '<span class="mw_display_point '
			. (!$mwRewardPoint ? 'hide' : 'show')
			. '" style="display: inline-block; margin-bottom: 20px">'
			. $hidden
			. $rewardIcon
			. __("You will earn <b>%1</b> for buying this product.", $this->_dataHelper->formatPoints($mwRewardPoint))
			. '</span>';
		}

		return $html;
	}

	/**
	 * Display sell points in Category/Product pages
	 *
	 * @param \Magento\Catalog\Model\Product $_product
	 * @return string
	 */
	public function getDisplaySellPoints($_product)
	{
		$sellPoints = (int) $this->_productFactory->create()->load($_product->getId())->getMwRewardPointSellProduct();
		if ($sellPoints > 0 && $this->_dataHelper->moduleEnabled()) {
			return '<div class="price-box"><span class="price">'
			. __("Sell in %1 Points", $sellPoints)
			. '</span></div>';
		}

		return '';
	}

	/**
	 * Display points in My Account (top link)
	 *
	 * @param  \Magento\Customer\Block\Account\Link $_link
	 * @return html
	 */
	public function getPointCustomerShowTop($_link)
	{
		if (strpos($_link->getUrl(), 'customer/account') == true
			&& strpos($_link->getUrl(), 'customer/account/login') == false
			&& strpos($_link->getUrl(), 'customer/account/logout') == false
		) {
			$customerId = $this->_customerSession->getCustomer()->getId();
			if ($customerId && $this->_dataHelper->moduleEnabled()) {
				$customer = $this->_customerFactory->create()->load($customerId);
				if ($customer) {
					if ($customer->getMwRewardPoint() > 0) {
						return '<span style="color:yellow"> (<a href="'
						. $this->_urlBuilder->getUrl("rewardpoints/rewardpoints")
						. '" style="color:yellow">'
						. $this->_dataHelper->formatPoints($customer->getMwRewardPoint())
						. '</a>)</span>';
					}
				}
			}
		}

		return '';
	}

	/**
	 * Display points balance in My Account tab
	 *
	 * @param  Magento\Framework\View\Element\Html\Link\Current $_link
	 * @return html
	 */
	public function getPointCustomerShowMyAccount($_link)
	{
		if ($_link->getName() == 'reward_points' && $this->_dataHelper->moduleEnabled()) {
			$customerId = $this->_customerSession->getCustomer()->getId();
			if ($customerId) {
				$customer = $this->_customerFactory->create()->load($customerId);
				if ($customer->getMwRewardPoint() > 0) {
					return ' ('.$this->_dataHelper->formatPoints($customer->getMwRewardPoint()).')';
				}
			}

			return '(0)';
		}

		return '';
	}

	/**
	 * Display the Facebook Like button
	 *
	 * @return html
	 */
	public function getDisplayFacebookLike()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			return $this->_layoutFactory->create()->createBlock(
				'MW\RewardPoints\Block\Facebook\Like'
			)->setTemplate(
				'MW_RewardPoints::facebook/likebutton.phtml'
			)->toHtml();
		}

		return '';
	}

	/**
	 * Display the invitation block
	 *
	 * @return html
	 */
	public function getInvitationTemplate()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			return $this->_layoutFactory->create()->createBlock(
				'MW\RewardPoints\Block\Invitation\Form'
			)->setTemplate(
				'MW_RewardPoints::customer/account/invitation/invite_form_ajax.phtml'
			)->toHtml();
		}

		return '';
	}

	/**
	 * Display Reward Points in Checkout One Page
	 *
	 * @return html
	 */
	public function getRewardpointOnepageTemplate()
    {
        if ($this->_dataHelper->moduleEnabled()) {
        	$block = $this->_layoutFactory->create()->createBlock(
            	'MW\RewardPoints\Block\Checkout\Cart\Rewardpoints'
            );

            if ($this->_dataHelper->isOSCRunning()) {
                $block->setTemplate(
                	'MW_RewardPoints::checkout/onepage/rewardpoints_osc.phtml'
                );
            } else {
                $block->setTemplate(
                	'MW_RewardPoints::checkout/onepage/rewardpoints.phtml'
                );
            }

            return '<div id="mw-checkout-payment-rewardpoints">'.$block->toHtml().'</div>';
        }

        return '';
    }

    /**
	 * Display point balance of customer on any pages
	 *
	 * @return html
	 */
    public function getCustomerPoint()
    {
        $enable = $this->_dataHelper->moduleEnabled();
        $customerId = $this->_customerSession->getCustomer()->getId();

        if ($customerId && $enable) {
            $customer = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();

            if ($customer->getMwRewardPoint() > 0) {
            	return '<span> (' . $this->_dataHelper->formatPoints($customer->getMwRewardPoint()) . ')</span>';
            }
        }

        return '';
    }

	/**
	 * Display earn points in mini cart
	 *
	 * @return html
	 */
	public function getDisplayEarnpointMiniCart()
	{
		if ($this->_dataHelper->moduleEnabled()) {
			$rewardPoint = (int) $this->_checkoutSession->getQuote()->getEarnRewardpoint();

			if ($rewardPoint > 0) {
				$rewardIcon = $this->_dataHelper->getRewardIconHtml();
				return '<span class="mw_display_point" style="display: block; text-align: center; margin-bottom: 20px">' . $rewardIcon . __("You will earn <b>%1</b>", $this->_dataHelper->formatPoints($rewardPoint)) . '</span>';
			}
		}

		return '';
	}
}
