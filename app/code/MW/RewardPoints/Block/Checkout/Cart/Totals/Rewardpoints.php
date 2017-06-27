<?php

namespace MW\RewardPoints\Block\Checkout\Cart\Totals;

class Rewardpoints extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_checkoutSession = $checkoutSession;
	}

	public function getCheckoutSession()
	{
		return $this->_checkoutSession;
	}
}
