<?php

namespace MW\RewardPoints\Block\Checkout\Onepage\Review\Totals;

class Rewardpoints extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @var \Magento\Framework\Module\Manager
	 */
	protected $_moduleManager;

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\Module\Manager $moduleManager
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\Module\Manager $moduleManager,
		\MW\RewardPoints\Helper\Data $dataHelper,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_checkoutSession = $checkoutSession;
		$this->_moduleManager = $moduleManager;
		$this->_dataHelper = $dataHelper;
	}

	/**
	 * Retrive checkout session
	 *
	 * @return \Magento\Checkout\Model\Session
	 */
	public function getCheckoutSession()
	{
		return $this->_checkoutSession;
	}

	/**
	 * Retrive column span number
	 *
	 * @return integer
	 */
	public function getColumnSpan()
	{
		$colspan = 3;

		if ($this->_moduleManager->isOutputEnabled('MW_Onestepcheckout')) {
			if ($this->_dataHelper->getStoreConfig('onestepcheckout/general/allowremoveproduct')) {
				$colspan++;
			}

			if ($this->_dataHelper->getStoreConfig('onestepcheckout/addfield/showimageproduct')) {
				$colspan++;
			}
		}

		return $colspan;
	}
}
