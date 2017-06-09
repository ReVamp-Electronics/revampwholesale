<?php

namespace MW\RewardPoints\Block\Checkout\Cart;

class Rewardpoints extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

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
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param \MW\RewardPoints\Model\CustomerFactory $customerFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Customer\Model\Session $customerSession,
		\MW\RewardPoints\Helper\Data $dataHelper,
		\MW\RewardPoints\Model\CustomerFactory $customerFactory,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_checkoutSession = $checkoutSession;
		$this->_customerSession = $customerSession;
		$this->_dataHelper = $dataHelper;
		$this->_customerFactory = $customerFactory;
	}

	protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    public function _getCustomer()
    {
    	return $this->_customerSession->getCustomer();
    }

    public function _getQuote()
    {
    	return $this->_getCheckoutSession()->getQuote();
    }

    public function getRewardPoints()
    {
    	return $this->_getQuote()->getMwRewardpoint();
    }

    public function getPointPerMoney()
	{
		$config = $this->_dataHelper->getPointMoneyRateConfig();
		$rate = explode("/", $config);

		return $rate;
	}

	public function getCurrentRewardPoints()
	{
		$customer = $this->_customerFactory->create()->load(
			$this->_getCustomer()->getId()
		);

		return $customer->getMwRewardPoint();
	}

	public function getRewardPointsRule()
	{
		return $this->_dataHelper->getCheckoutRewardPointsRule($this->_getQuote());
	}

	public function getMaxPointsToCheckout()
	{
    	return $this->_dataHelper->getMaxPointToCheckOut();
	}
}
