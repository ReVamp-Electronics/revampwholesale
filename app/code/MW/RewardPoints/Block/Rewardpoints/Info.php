<?php

namespace MW\RewardPoints\Block\Rewardpoints;

class Info extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \MW\RewardPoints\Model\CustomerFactory $customerFactory
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\MW\RewardPoints\Model\CustomerFactory $customerFactory,
		\MW\RewardPoints\Helper\Data $dataHelper,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_customerSession = $customerSession;
		$this->_customerFactory = $customerFactory;
		$this->_dataHelper = $dataHelper;
	}

	public function getRewardPoints()
	{
		$member = $this->_customerFactory->create()->load(
			$this->_customerSession->getCustomer()->getId()
		);

		return $member->getMwRewardPoint();
	}

	public function getPointPerMoney()
	{
		$config = $this->_dataHelper->getPointMoneyRateConfig();
		$rate = explode("/", $config);

		return $rate;
	}

	public function getPointPerCredit()
	{
		$config = $this->_dataHelper->pointCreditRate();
		$rate = explode("/", $config);

		return $rate;
	}

	public function formatMoney($money)
	{
		return $this->_dataHelper->formatMoney($money);
	}

	public function getMoney()
	{
		return $this->formatMoney(
			$this->_dataHelper->exchangePointsToMoneys($this->getRewardPoints())
		);
	}

	public function canExchangeToCredit()
	{
		return $this->_dataHelper->allowExchangePointToCredit() && $this->_dataHelper->getCreditModule();
	}
}
