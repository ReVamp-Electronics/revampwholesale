<?php

namespace MW\RewardPoints\Block\Adminhtml\Sales\Order\Create;

class Payment extends \Magento\Backend\Block\Template
{
	protected $_quote = null;

	/**
	 * @var \Magento\Sales\Model\AdminOrder\Create
	 */
	protected $_adminOrder;

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_memberFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\AdminOrder\Create $adminOrder
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Sales\Model\AdminOrder\Create $adminOrder,
		\MW\RewardPoints\Helper\Data $dataHelper,
		\MW\RewardPoints\Model\CustomerFactory $memberFactory,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_adminOrder = $adminOrder;
		$this->_dataHelper = $dataHelper;
		$this->_memberFactory = $memberFactory;
	}

    /**
     * @return \Magento\Quote\Model\Quote|null
     */
	public function _getQuote()
    {
    	if ($this->_quote == null) {
    		$this->_quote = $this->_adminOrder->getQuote();
    	}

        return $this->_quote;
    }

    /**
     * @return array
     */
    public function getRewardPointsRule()
    {
        $quote = $this->_getQuote();
        $storeCode = $quote->getStore()->getCode();

        return $this->_dataHelper->getCheckoutRewardPointsRule($quote, $storeCode);
    }

    /**
     * @return html
     */
    public function getCurrentRewardPoints()
    {
        $quote       = $this->_getQuote();
        $storeCode   = $quote->getStore()->getCode();
        $customer    = $this->_memberFactory->create()->load($quote->getCustomerId());
        $point       = (int) $customer->getMwRewardPoint();
        $pointShow   = $this->_dataHelper->formatPoints($point, $storeCode);

        return $pointShow;
    }

    /**
     * @return array
     */
    public function getRate()
    {
    	$storeCode = $this->_getQuote()->getStore()->getCode();
        $rate = explode("/", $this->_dataHelper->getPointMoneyRateConfig($storeCode));

        return $rate;
    }

    /**
     * @return html
     */
    public function getEarnPointShow()
    {
        $quote               = $this->_getQuote();
        $storeCode           = $quote->getStore()->getCode();
        $earnRewardPoint     = (int) $quote->getEarnRewardpoint();
        $earnRewardPointShow = $this->_dataHelper->formatPoints($earnRewardPoint, $storeCode);

        return $earnRewardPointShow;
    }

    /**
     * @return mixed
     */
    public function getRewardPoints()
    {
        return $this->_getQuote()->getMwRewardpoint();
    }

    /**
     * @return html
     */
    public function getMaxPointToCheckOut()
    {
        $quote = $this->_getQuote();
        $quote->collectTotals()->save();
        $storeCode	= $quote->getStore()->getCode();
        $spendPoint = $this->_dataHelper->getMaxPointToCheckOut(
        	$quote,
        	$quote->getCustomerId(),
        	$storeCode,
        	$quote->getBaseGrandTotal()
        );
        $spendPointShow = $this->_dataHelper->formatPoints($spendPoint, $storeCode);

        return $spendPointShow;
    }
}
