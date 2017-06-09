<?php

namespace MW\RewardPoints\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
	/**
	 * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
	 */
	protected $_historyFactory;

	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
	 * @param array $data
	 */
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
		parent::__construct($context, $registry, $resource, $resourceCollection);
		$this->_historyFactory = $historyFactory;
		$this->_customerFactory = $customerFactory;
	}

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('MW\RewardPoints\Model\ResourceModel\Customer');
	}

	/**
	 * Add reward point for customer
	 *
	 * @param int $point
	 * @return void
	 */
	public function addRewardPoint($point)
	{
		$point = (int) $point;
		$this->setMwRewardPoint($this->getMwRewardPoint() + $point);
		$this->save();
	}

	/**
	 * Get friend of customer
	 *
	 * @return this|false
	 */
	public function getFriend()
	{
		$friendId = $this->getMwFriendId();
		if ($friendId) {
			return $this->load($friendId);
		}

		return false;
	}

	/**
	 * Save transaction history
	 *
	 * @param  array  $data
	 * @return void
	 */
	public function saveTransactionHistory(array $data = [])
	{
		$data['customer_id'] = $this->getId();
    	$history = $this->_historyFactory->create();
    	$history->setData($data);
    	$history->save();
	}

	/**
	 * @return \Magento\Customer\Model\Customer
	 */
	public function getCustomerModel()
	{
		return $this->_customerFactory->create()->load($this->getId());
	}
}
