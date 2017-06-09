<?php

namespace MW\RewardPoints\Block\Checkout\Cart;

class Banner extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CartrulesFactory
     */
    protected $_cartrulesFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
        $this->_cartrulesFactory = $cartrulesFactory;
    }

    public function _construct()
    {
        $this->setTemplate('MW_RewardPoints::checkout/cart/banner.phtml');
    }

    /**
     * @return array
     */
    public function getBannerRules()
    {
        $ruleBanner  = [];
        $quote       = $this->_checkoutSession->getQuote();
        $ruleDetails = unserialize($quote->getMwRewardpointRuleMessage());

        if ($ruleDetails) {
            foreach ($ruleDetails as $ruleDetail) {
                $detail = $this->_cartrulesFactory->create()->load($ruleDetail)->getPromotionImage();
                if ($detail != '') {
                    $ruleBanner[] = $detail;
                }
            }
        }

        return $ruleBanner;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->_dataHelper->moduleEnabled() || !$this->_dataHelper->getEnablePromotionBanner()) {
            return '';
        }

        return parent::_toHtml();
    }
}
