<?php

namespace MW\RewardPoints\Block\Checkout\Onepage;

class Totals extends \Magento\Framework\View\Element\Template
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
		$this->_checkoutSession = $checkoutSession;
		parent::__construct($context, $data);
	}

	/**
	 * Get quote
	 *
	 * @return \Magento\Quote\Model\Quote
	 */
	public function getQuote()
	{
		return $this->_checkoutSession->getQuote();
	}
}
