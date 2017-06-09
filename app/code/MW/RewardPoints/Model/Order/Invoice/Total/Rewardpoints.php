<?php

namespace MW\RewardPoints\Model\Order\Invoice\Total;

class Rewardpoints extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
	/**
     * Collect reward points and redeem points
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
    	$order = $invoice->getOrder();

        $totalDiscountAmount     = $order->getMwRewardpointDiscountShow();
        $baseTotalDiscountAmount = $order->getMwRewardpointDiscount();

        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
        $invoice->setMwRewardpoint($order->getMwRewardpoint());
        $invoice->setMwRewardpointDiscountShow($totalDiscountAmount);
        $invoice->setMwRewardpointDiscount($baseTotalDiscountAmount);

        return $this;
    }
}
