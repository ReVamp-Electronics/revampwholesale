<?php

namespace MW\RewardPoints\Controller\Checkout;

class Rewardpointspost extends \MW\RewardPoints\Controller\Checkout
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
	    	->collectTotals()
	    	->save();

        $layout = $this->_objectManager->get(
            'Magento\Framework\View\LayoutFactory'
        )->create();
        $update = $layout->getUpdate();
        $update->load('rewardpoints_checkout_rewardpointspost');
        $layout->generateXml();
        $layout->generateElements();
        $output = $layout->getBlock('checkout.cart.totals')->toHtml();

        echo $output;
        exit;
	}
}
