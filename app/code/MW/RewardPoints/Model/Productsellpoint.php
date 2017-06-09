<?php

namespace MW\RewardPoints\Model;

class Productsellpoint extends \Magento\Framework\Model\AbstractModel
{
	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('MW\RewardPoints\Model\ResourceModel\Productsellpoint');
	}
}
