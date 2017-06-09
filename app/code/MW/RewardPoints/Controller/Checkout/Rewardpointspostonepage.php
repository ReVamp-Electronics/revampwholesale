<?php

namespace MW\RewardPoints\Controller\Checkout;

class Rewardpointspostonepage extends \MW\RewardPoints\Controller\Checkout
{
	public function execute()
	{
    	$step = $this->_dataHelper->getPointStepConfig();
    	$rewardpoints = $this->getRequest()->getParam('rewardpoints');
    	if ($rewardpoints < 0) {
    		$rewardpoints = -$rewardpoints;
    	}
    	$rewardpoints = round(($rewardpoints / $step), 0) * $step;
    	if ($rewardpoints >= 0) {
    		$this->_dataHelper->setPointToCheckOut($rewardpoints);
    	}

    	$this->_objectManager->get('Magento\Checkout\Model\Session')
	    	->getQuote()
			->setTotalsCollectedFlag(false)
	    	->collectTotals()
	    	->save();

        $output = $this->_objectManager->get(
            'Magento\Checkout\Model\CompositeConfigProvider'
        )->getConfig();

		// Reload redeem points
		if (isset($output['quoteData']['mw_rewardpoint_discount_show'])) {
			$redeemPoints = $output['quoteData']['mw_rewardpoint_discount_show'];
			if ($redeemPoints > 0) {
				$output['totalsData']['mw_rewardpoint_discount_show'] = $this->_dataHelper->formatMoney(
					-$redeemPoints,
					true,
					false
				);
			} else {
				$output['totalsData']['mw_rewardpoint_discount_show'] = 0;
			}
		} else {
			$output['totalsData']['mw_rewardpoint_discount_show'] = 0;
		}

		// Reload earn points
		if (isset($output['quoteData']['earn_rewardpoint'])) {
			$earnRewardPoint = $output['quoteData']['earn_rewardpoint'];
			if ($earnRewardPoint > 0) {
				$output['totalsData']['earn_rewardpoint'] = $this->_dataHelper->formatPoints($earnRewardPoint);
			} else {
				$output['totalsData']['earn_rewardpoint'] = 0;
			}
		} else {
			$output['totalsData']['earn_rewardpoint'] = 0;
		}

		// Reload sell points
		if (isset($output['quoteData']['mw_rewardpoint_sell_product'])
			&& isset($output['quoteData']['mw_rewardpoint'])
		) {
			$rewardpoints = (int) $output['quoteData']['mw_rewardpoint'];
			$rewardpointSellProduct = (int) $output['quoteData']['mw_rewardpoint_sell_product'];
			$totalRewardPoint = $rewardpoints + $rewardpointSellProduct;
			if ($totalRewardPoint && $rewardpointSellProduct) {
				$output['totalsData']['totals_rewardpoint'] = $this->_dataHelper->formatPoints($totalRewardPoint);
			}  else {
				$output['totalsData']['totals_rewardpoint'] = 0;
			}
		} else {
			$output['totalsData']['totals_rewardpoint'] = 0;
		}

        echo \Zend_Json::encode($output);
        exit;
	}
}
