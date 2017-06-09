<?php

namespace MW\RewardPoints\Model;

class Productpoint extends \Magento\Framework\Model\AbstractModel
{
	/**
	 * @var \Magento\Catalog\Model\ProductFactory
	 */
	protected $_productFactory;

	/**
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Catalog\Model\ProductFactory $productFactory
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
		$this->_productFactory = $productFactory;
	}

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('MW\RewardPoints\Model\ResourceModel\Productpoint');
	}

	/**
	 * Retrive reward points of product
	 *
	 * @param  int $productId
	 * @return int
	 */
	public function getPoint($productId)
	{
		$rewardPointsValue = 0;
		$product = $this->_productFactory->create()->load($productId);

		if ((int) $product->getRewardPointProduct() > 0) {
			$rewardPointsValue = $product->getRewardPointProduct();
		}

		return $rewardPointsValue;
	}
}
