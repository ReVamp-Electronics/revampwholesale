<?php

namespace MW\RewardPoints\Block\Adminhtml\Sellproducts\Edit\Tab;

class Headjs extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MW_RewardPoints::sellproducts/edit/tab/headjs.phtml');
    }
}
