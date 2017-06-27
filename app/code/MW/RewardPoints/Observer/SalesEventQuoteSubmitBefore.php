<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesEventQuoteSubmitBefore implements ObserverInterface
{
    /**
     * Set reward points to order from quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $address = $quote->getShippingAddress();

        $order->setMwRewardpoint($address->getMwRewardpoint());
        $order->setMwRewardpointDiscount($address->getMwRewardpointDiscount());
        $order->setMwRewardpointDiscountShow($address->getMwRewardpointDiscountShow());

        return $this;
    }
}
