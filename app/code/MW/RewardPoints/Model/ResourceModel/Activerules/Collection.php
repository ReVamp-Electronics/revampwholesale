<?php

namespace MW\RewardPoints\Model\ResourceModel\Activerules;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
    	$this->_init(
    		'MW\RewardPoints\Model\Activerules',
    		'MW\RewardPoints\Model\ResourceModel\Activerules'
    	);
    }

    /**
     * @return void
     */
    protected function _afterLoad()
    {
        foreach ($this as $item) {
            $storeView = $item->getStoreView();
            $store     = explode(",", $storeView);
            $item->setData('store_view', $store);
        }

        parent::_afterLoad();
    }
}
