<?php

namespace MW\RewardPoints\Model\ResourceModel\Rewardpointshistory\Rewarded;

use MW\RewardPoints\Model\Status;

class Collection extends \MW\RewardPoints\Model\ResourceModel\Rewardpointshistory\Collection
{
	protected $_useType = [1, 2, 3, 4, 5, 6, 14, 8, 30, 15, 16, 12, 18, 21, 32, 25, 29, 26, 27, 19, 50, 51, 52, 53];
	protected $_groupOrder = [3, 8, 30];
	protected $_groupBirthday = [26];
	protected $_groupReferal = [4, 5, 6, 14];
	protected $_groupOther = [25, 29, 15, 12, 18, 21, 32, 27, 19, 50, 51, 53, 52];

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
	 * @param \Magento\Framework\Event\ManagerInterface $eventManager
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
	 * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
	 */
	public function __construct(
		\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
		\Magento\Framework\Event\ManagerInterface $eventManager,
		\MW\RewardPoints\Helper\Data $dataHelper,
		\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
		\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
	) {
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
		$this->_dataHelper = $dataHelper;
	}

	/**
	 * Join fields
	 *
	 * @param string $fromDate
	 * @param string $toDate
	 * @return $this
	 */
	protected function _joinFields($fromDate = '', $toDate = '')
	{
		$where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
		$this->getSelect()->where($where . ' >= "' . $fromDate . '" AND ' . $where . ' <= "' . $toDate . '"');

		$this->addFieldToFilter('status', Status::COMPLETE);
		$this->addFieldToFilter('status_check', ['neq' => Status::REFUNDED]);

		$this->addExpressionFieldToSelect(
			'total_rewarded_sum',
			'sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0))',
			'total_rewarded_sum'
		);
		$this->addExpressionFieldToSelect(
			'rewarded_on_purchases_sum',
			'sum(if(type_of_transaction IN ('.implode(",", $this->_groupOrder).'),amount,0))',
			'rewarded_on_purchases_sum'
		);
		$this->addExpressionFieldToSelect(
			'rewarded_on_sign_up_sum',
			'sum(if(type_of_transaction IN (1),amount,0))',
			'rewarded_on_sign_up_sum'
		);
		$this->addExpressionFieldToSelect(
			'rewarded_on_subscribers_sum',
			'sum(if(type_of_transaction IN (16),amount,0))',
			'rewarded_on_subscribers_sum'
		);
		$this->addExpressionFieldToSelect(
			'rewarded_on_reviews_sum',
			'sum(if(type_of_transaction IN (2),amount,0))',
			'rewarded_on_reviews_sum'
		);
		$this->addExpressionFieldToSelect(
			'rewarded_on_birthday_sum',
			'sum(if(type_of_transaction IN ('.implode(",", $this->_groupBirthday).'),amount,0))',
			'rewarded_on_birthday_sum'
		);
		$this->addExpressionFieldToSelect(
			'rewarded_on_referal_sum',
			'sum(if(type_of_transaction IN ('.implode(",", $this->_groupReferal).'),amount,0))',
			'rewarded_on_referal_sum'
		);
		$this->addExpressionFieldToSelect(
			'added_by_admin_sum',
			'sum(if(type_of_transaction IN (12),amount,0))',
			'added_by_admin_sum'
		);
		$this->addExpressionFieldToSelect(
			'other_rewards_sum',
			'sum(if(type_of_transaction IN ('.implode(",", $this->_groupOther).'),amount,0))',
			'other_rewards_sum'
		);
		$this->addExpressionFieldToSelect(
			'total_transaction_count',
			'count( distinct if(type_of_transaction IN ('.implode(",", $this->_useType).'),history_id,null))',
			'total_transaction_count'
		);

		return $this;
	}

	/**
	 * Set date range
	 *
	 * @param string $fromDate
	 * @param string $toDate
	 * @return $this
	 */
	public function setDateRange($fromDate, $toDate)
	{
		$this->_reset()->_joinFields($fromDate, $toDate);
		return $this;
	}

	/**
	 * Set store filter collection
	 *
	 * @param array $storeIds
	 * @return $this
	 */
	public function setStoreIds($storeIds)
	{
		return $this;
	}
}
