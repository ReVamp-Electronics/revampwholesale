<?php

namespace MW\RewardPoints\Model\ResourceModel\Rewardpointshistory\Redeemed;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Collection extends \MW\RewardPoints\Model\ResourceModel\Rewardpointshistory\Collection
{
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
        $orderTable        = $this->_resource->getTable('sales_order');
        $rewardOrderTable = $this->_resource->getTable('mw_reward_point_order');

        $where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
        $this->getSelect()->where($where . ' >= "' . $fromDate . '" AND ' . $where . ' <= "' . $toDate . '"');

        $this->addFieldToFilter('main_table.status', Status::COMPLETE);
        $this->addFieldToFilter('status_check', ['neq' => Status::REFUNDED]);
        $this->addFieldToFilter('main_table.type_of_transaction', Type::USE_TO_CHECKOUT);

        $this->getSelect()->joinLeft(
            ['reward_order_entity' => $rewardOrderTable],
            'main_table.history_order_id = reward_order_entity.order_id',
            ['money']
        );
        $this->getSelect()->joinLeft(
            ['order_entity' => $orderTable],
            'main_table.history_order_id = order_entity.entity_id',
            ['base_grand_total']
        );
        $this->addExpressionFieldToSelect(
            'total_redeemed_sum',
            'sum(amount)',
            'total_redeemed_sum'
        );
        $this->addExpressionFieldToSelect(
            'order_id_count',
            'count(distinct if(history_order_id != 0,history_order_id,null))',
            'order_id_count'
        );
        $this->addExpressionFieldToSelect(
            'avg_redeemed_per_order',
            '(sum(amount)/count(distinct if(history_order_id != 0 && status_check != '.Status::REFUNDED.',history_order_id,null)))',
            'avg_redeemed_per_order'
        );
        $this->addExpressionFieldToSelect(
            'total_point_discount_sum',
            'sum(money)',
            'total_point_discount_sum'
        );
        $this->addExpressionFieldToSelect(
            'total_sales_sum',
            'sum(base_grand_total)',
            'total_sales_sum'
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
