<?php

namespace MW\RewardPoints\Block\Invitation;

class Form extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_customerSession = $customerSession;
	}

	/**
	 * Retrive current customer session
	 *
	 * @return Magento\Customer\Model\Customer
	 */
	public function getCustomer()
	{
		return $this->_customerSession->getCustomer();
	}
}
