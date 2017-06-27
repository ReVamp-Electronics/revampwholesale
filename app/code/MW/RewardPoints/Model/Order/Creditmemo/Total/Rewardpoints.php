<?php

namespace MW\RewardPoints\Model\Order\Creditmemo\Total;

class Rewardpoints extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
	/**
     * Collect reward points and redeem points
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
	public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $totalDiscountAmount     = $order->getMwRewardpointDiscountShow();
        $baseTotalDiscountAmount = $order->getMwRewardpointDiscount();

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);
        $creditmemo->setMwRewardpoint($order->getMwRewardpoint());
        $creditmemo->setMwRewardpointDiscountShow($totalDiscountAmount);
        $creditmemo->setMwRewardpointDiscount($baseTotalDiscountAmount);

        return $this;
    }
}
