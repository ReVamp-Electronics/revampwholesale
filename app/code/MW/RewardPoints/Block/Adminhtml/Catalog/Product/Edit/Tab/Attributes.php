<?php

namespace MW\RewardPoints\Block\Adminhtml\Catalog\Product\Edit\Tab;

class Attributes extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
{
	protected function _prepareForm()
    {
        parent::_prepareForm();

        $group = $this->getGroup();
        if ($group) {
            $sellPointProduct = $this->getForm()->getElement('mw_reward_point_sell_product');

            if ($sellPointProduct) {
                $sellPointProduct->setRenderer(
                    $this->getLayout()->createBlock(
                    	'MW\RewardPoints\Block\Adminhtml\Renderer\Catalog\Product\Edit\Tab\Attributes\Sell'
                    )
                );
            }

            $rewardPointProduct = $this->getForm()->getElement('reward_point_product');
            if ($rewardPointProduct) {
                $rewardPointProduct->setRenderer(
                    $this->getLayout()->createBlock(
                    	'MW\RewardPoints\Block\Adminhtml\Renderer\Catalog\Product\Edit\Tab\Attributes\Reward'
                    )
                );
            }
        }
    }
}
