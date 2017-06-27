<?php

namespace MW\RewardPoints\Model;

class Rewardpointshistory extends \Magento\Framework\Model\AbstractModel
{
	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_memberFactory;

	/**
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
	 * @param array $data
	 */
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
		parent::__construct($context, $registry, $resource, $resourceCollection);
		$this->_memberFactory = $memberFactory;
	}

	/**
     * Define resource model
     *
     * @return void
     */
	protected function _construct()
	{
		$this->_init('MW\RewardPoints\Model\ResourceModel\Rewardpointshistory');
	}

	/**
	 * Get size of transaction history for customer
	 *
	 * @param  int $customerId
	 * @param  int $type
	 * @param  int|string $transactionDetail
	 * @param  int $status
	 * @return int
	 */
	public function sizeofTransactionHistory(
		$customerId,
		$type,
		$transactionDetail = null,
		$status = null
	) {
		$collection = $this->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToFilter('type_of_transaction', $type);

		if ($transactionDetail != null) {
			$collection->addFieldToFilter('transaction_detail', $transactionDetail);
		}

		if ($status != null) {
			$collection->addFieldToFilter('status', $status);
		}

		return (int) $collection->getSize();
	}

	/**
	 * Retrive customer
	 *
	 * @return \MW\RewardPoints\Model\Customer
	 */
	public function getCustomer()
    {
    	return $this->_memberFactory->create()->load($this->getCustomerId());
    }
}
