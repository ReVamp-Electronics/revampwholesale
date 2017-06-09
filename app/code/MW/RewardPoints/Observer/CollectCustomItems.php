<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class CollectCustomItems implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Add Redeem Points amount to paypal payment
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->_checkoutSession->getQuote();
        if (abs($quote->getMwRewardpointDiscount()) > 0.0001) {
            $cart = $observer->getEvent()->getCart();
            $cart->addCustomItem(
                'Rewardpoint discount',
                1,
                (float) $quote->getMwRewardpointDiscount() * (-1)
            );
        }

        return $this;
    }
}
