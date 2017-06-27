<?php

namespace MW\RewardPoints\Model\ResourceModel\Rewardpointshistory\Overview;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Collection extends \MW\RewardPoints\Model\ResourceModel\Rewardpointshistory\Collection
{
    protected $_useType = [1, 2, 3, 4, 5, 6, 14, 8, 30, 15, 16, 12, 18, 21, 32, 25, 29, 26, 27, 19, 50, 51, 52, 53];

    /**
     * Join fields
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->addFieldToFilter(
            'transaction_time',
            [
                'from' => $fromDate,
                'to' => $toDate,
                'datetime' => true
            ]
        );
        $this->addFieldToFilter('status', Status::COMPLETE);
        $this->addFieldToFilter('status_check', ['neq' => Status::REFUNDED]);

        $this->addExpressionFieldToSelect(
            'total_rewarded_sum',
            'sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0))',
            'total_rewarded_sum'
        );
        $this->addExpressionFieldToSelect(
            'total_redeemed_sum',
            'sum(if(type_of_transaction IN ('.Type::USE_TO_CHECKOUT.') && status_check != '.Status::REFUNDED.',amount,0))',
            'total_redeemed_sum'
        );
        $this->addExpressionFieldToSelect(
            'sign_up_count',
            'count(distinct if(type_of_transaction IN (1),history_id,null))',
            'sign_up_count'
        );
        $this->addExpressionFieldToSelect(
            'order_id_count',
            'count(distinct if(type_of_transaction IN (6,8,11,14,30) and history_order_id != 0,history_order_id,null))',
            'order_id_count'
        );
        $this->addExpressionFieldToSelect(
            'customer_id_count',
            'count(distinct customer_id)',
            'customer_id_count'
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
