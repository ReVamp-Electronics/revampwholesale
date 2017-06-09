<?php

namespace MW\RewardPoints\Block\Adminhtml\Sales\Order\Create\Totals;

class Rewardpoints extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_sessionQuote = $sessionQuote;
    }

    public function getSessionQuote()
    {
        return $this->_sessionQuote;
    }
}
