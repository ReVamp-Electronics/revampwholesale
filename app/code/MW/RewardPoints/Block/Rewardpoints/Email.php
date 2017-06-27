<?php

namespace MW\RewardPoints\Block\Rewardpoints;

class Email extends \Magento\Framework\View\Element\Template
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
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \MW\RewardPoints\Model\CustomerFactory $customerFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\MW\RewardPoints\Model\CustomerFactory $customerFactory,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_customerSession = $customerSession;
		$this->_customerFactory = $customerFactory;
	}

	/**
	 * Retrive Subscribed Balance Updating
	 *
	 * @return bool
	 */
	public function getSubscribedBalanceUpdate()
	{
		$member = $this->_customerFactory->create()->load(
			$this->_customerSession->getCustomer()->getId()
		);

		return $member->getSubscribedBalanceUpdate();
	}
}
