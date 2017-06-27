<?php

namespace MW\RewardPoints\Model;

use MW\RewardPoints\Model\Admin\ReportRage;
use MW\RewardPoints\Model\Status;
use MW\RewardPoints\Model\Type;

class Report extends \Magento\Framework\Model\AbstractModel
{
    protected $_allMonths = 0;

    protected $_useType = [1, 2, 3, 4, 5, 6, 14, 8, 30, 15, 16, 12, 18, 21, 32, 25, 29, 26, 27, 19, 50, 51, 52, 53];

    protected $_groupSignup = [1];

    protected $_groupReview = [2];

    protected $_groupOrder = [3, 8, 30];

    protected $_groupBirthday = [26];

    protected $_groupNewsletter = [16];

    protected $_groupTag = [];

    protected $_groupSocial = [];

    protected $_groupReferal = [4, 5, 6, 14];

    protected $_groupOther = [25, 29, 15, 12, 18, 21, 32, 27, 19, 50, 51, 53, 52];

    protected $_constTypeRewardPoints;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_appResource;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $appResource
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $appResource,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_customerFactory = $customerFactory;
        $this->_localeDate = $localeDate;
        $this->_dateFormat = $dateFormat;
        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->_appResource = $appResource;
        $this->_memberFactory = $memberFactory;
        $this->_historyFactory = $historyFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function prepareCollection($data)
    {
        $rewardOrderTable = $this->_appResource->getTableName('mw_reward_point_order');

        if ($data['report_range'] == ReportRage::REPORT_RAGE_CUSTOM) {
            if ($this->_validationDate($data) == false) {
                return;
            }
            /** Get all month between two dates */
            $this->_allMonths = $this->_get_months($data['from'], $data['to']);
        }

        $users = [];
        $collection = $this->_customerFactory->create()->getCollection();
        foreach ($collection->getData() as $user) {
            $users[] = $user['entity_id'];
        }

        /** Query to get total balance of customers */
        $collection = $this->_memberFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('customer_id', ['in' => $users]);
        $collection->addExpressionFieldToSelect('total_point', 'SUM(mw_reward_point)', 'total_point');
        $collectionCustomer = $collection->getFirstItem();

        /** Query to get redeemd */
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status_check', ['neq' => Status::REFUNDED]);
        $collection->addFieldToFilter('main_table.type_of_transaction', Type::USE_TO_CHECKOUT);
        $collection->getSelect()->joinLeft(
            ['reward_order_entity' => $rewardOrderTable],
            'main_table.history_order_id = reward_order_entity.order_id',
            []
        );
        $collection->addExpressionFieldToSelect('total_redeemed_sum', 'SUM(amount)', 'total_redeemed_sum');
        $this->_buildCollection($collection, $data);
        $collectionRedeemed = $collection;

        /** Query to get reward */
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status_check', ['neq' => Status::REFUNDED]);
        $collection->addExpressionFieldToSelect(
            'total_rewarded_sum',
            'sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0))',
            'total_rewarded_sum'
        );
        $this->_buildCollection($collection, $data);
        $collectionReward = $collection;

        /** Query to statistic */
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status_check', ['neq' => Status::REFUNDED]);
        $collection->addExpressionFieldToSelect(
            'total_rewarded_sum',
            'sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0))',
            'total_rewarded_sum'
        );
        $collection->addExpressionFieldToSelect(
            'total_rewarded_on_order_sum',
            'sum(if(type_of_transaction IN ('.implode(",", $this->_groupOrder).'),amount,0))',
            'total_rewarded_on_order_sum'
        );
        $collection->addExpressionFieldToSelect(
            'total_redeemed_sum',
            'sum(if(type_of_transaction IN ('.Type::USE_TO_CHECKOUT.') && status_check != '.Status::REFUNDED.',amount,0))',
            'total_redeemed_sum'
        );
        $collection->addExpressionFieldToSelect(
            'avg_reward_per_customer',
            '(sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0)) / count(distinct if(customer_id != 0 && type_of_transaction NOT IN ('.Type::USE_TO_CHECKOUT.', 17) && status_check != '.Status::REFUNDED.', customer_id,null)))',
            'avg_redeemed_per_customer'
        );
        $collection->addExpressionFieldToSelect(
            'avg_redeemed_per_order',
            '(sum(if(type_of_transaction IN ('.Type::USE_TO_CHECKOUT.'),amount,0)) / count(distinct if(history_order_id != 0 && type_of_transaction = '.Type::USE_TO_CHECKOUT.' && status_check != '.Status::REFUNDED.', history_order_id, null)))',
            'avg_redeemed_per_order'
        );
        $collection->addExpressionFieldToSelect(
            'avg_rewarded_per_order',
            '(sum(if(type_of_transaction IN ('.implode(",", $this->_groupOrder).'),amount,0)) / count(distinct if(history_order_id != 0 && status_check != '.Status::REFUNDED.' && type_of_transaction != '.Type::USE_TO_CHECKOUT.',history_order_id,null)))',
            'avg_rewarded_per_order'
        );
        $collection->addExpressionFieldToSelect(
            'total_order',
            'count(distinct if(history_order_id != 0 && status_check != '.Status::REFUNDED.',history_order_id,null))',
            'total_order'
        );
        $this->_buildCollection($collection, $data, false);
        $collectionStats = $collection;

        /** Query to get number of orders */
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('count(distinct if(history_order_id != 0, history_order_id, null)) as total_orders');
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status_check', ['neq' => Status::REFUNDED]);
        $collection->addFieldToFilter('main_table.history_order_id', ['gt' => 0]);
        $this->_buildCollection($collection, $data);
        $collectionOrder = $collection;

        switch ($data['report_range']) {
            case ReportRage::REPORT_RAGE_LAST_24H:
                $_time = $this->getPreviousDateTime(24);
                $start_24h_time = $this->_localeDate->formatDateTime(
                    date('Y-m-d h:i:s', $_time),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::MEDIUM
                );
                $start_24h_time = strtotime($start_24h_time);
                $start_time = [
                    'h'   => (int)date('H', $start_24h_time),
                    'd'   => (int)date('d', $start_24h_time),
                    'm'   => (int)date('m', $start_24h_time),
                    'y'   => (int)date('Y', $start_24h_time),
                ];
                $rangeDate = $this->_buildArrayDate(ReportRage::REPORT_RAGE_LAST_24H, $start_time['h'], $start_time['h'] + 24, $start_time);
                $_data = $this->_buildResult($collectionRedeemed, $collectionReward, $collectionOrder, 'hour', $rangeDate);
                $_data['report']['date_start'] = $start_time;
                break;
            case ReportRage::REPORT_RAGE_LAST_WEEK:
                $start_time = strtotime("-6 day", strtotime("Sunday Last Week"));
                $startDay = date('d', $start_time);
                $endDay = date('d',strtotime("Sunday Last Week"));
                $rangeDate = $this->_buildArrayDate(ReportRage::REPORT_RAGE_LAST_WEEK, $startDay, $endDay);
                $_data = $this->_buildResult($collectionRedeemed, $collectionReward, $collectionOrder, 'day', $rangeDate);
                $_data['report']['date_start'] = [
                    'd'   => (int)date('d', $start_time),
                    'm'   => (int)date('m', $start_time),
                    'y'   => (int)date('Y', $start_time),
                ];
                break;
            case ReportRage::REPORT_RAGE_LAST_MONTH:
                $last_month_time = strtotime($this->_getLastMonthTime());
                $last_month = date('m', $last_month_time);
                $start_day = 1;
                $end_day = $this->_days_in_month($last_month);
                $rangeDate = $this->_buildArrayDate(ReportRage::REPORT_RAGE_LAST_MONTH, $start_day, $end_day);
                $_data = $this->_buildResult($collectionRedeemed, $collectionReward, $collectionOrder, 'day', $rangeDate);
                $_data['report']['date_start'] = [
                    'd'   => $start_day,
                    'm'   => (int)$last_month,
                    'y'   => (int)date('Y', $last_month_time),
                    'total_day' => $end_day
                ];
                break;
            case ReportRage::REPORT_RAGE_LAST_7DAYS:
            case ReportRage::REPORT_RAGE_LAST_30DAYS:
                if ($data['report_range'] == ReportRage::REPORT_RAGE_LAST_7DAYS) {
                    $last_x_day = 7;
                } else if ($data['report_range'] == ReportRage::REPORT_RAGE_LAST_30DAYS) {
                    $last_x_day = 30;
                } else {
                    $last_x_day = 0;
                }

                $start_day = date('Y-m-d h:i:s', strtotime('-'.$last_x_day.' day', (new \DateTime())->getTimestamp()));
                $end_day = date('Y-m-d h:i:s', strtotime("-1 day"));
                $original_time = [
                    'from'  => $start_day,
                    'to'    => $end_day
                ];
                $rangeDate = $this->_buildArrayDate(ReportRage::REPORT_RAGE_CUSTOM, 0, 0, $original_time);
                $_data = $this->_buildResult($collectionRedeemed, $collectionReward, $collectionOrder, 'multiday', $rangeDate);
                break;
            case ReportRage::REPORT_RAGE_CUSTOM:
                $original_time = [
                    'from'  => $data['from'],
                    'to'    => $data['to']
                ];
                $rangeDate = $this->_buildArrayDate(ReportRage::REPORT_RAGE_CUSTOM, 0, 0, $original_time);
                $_data = $this->_buildResult($collectionRedeemed, $collectionReward, $collectionOrder, 'multiday', $rangeDate);
                break;
        }

        $_data['title'] = __('Rewarded / Redeemed Points');
        $_data['report_activities'] = $this->preapareCollectionPieChart($data);
        foreach ($collectionStats->getFirstItem()->getData() as $key => $stat) {
            $_data['statistics'][$key] = ($stat == null) ? 0 : number_format($stat, 0, '.', ',');
        }
        $_data['statistics']['total_point_customer'] =  number_format($collectionCustomer->getData('total_point'), 0, '.', ',');

        return json_encode($_data);
    }

    /**
     * @param $data
     * @return string
     */
    public function preapareCollectionPieChart($data)
    {
        if ($data['report_range'] == ReportRage::REPORT_RAGE_CUSTOM) {
            if ($this->_validationDate($data) == false) {
                return;
            }
            /** Get all month between two dates */
            $this->_allMonths = $this->_get_months($data['from'], $data['to']);
        }

        /** Query to get total rewards */
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status_check', ['neq' => Status::REFUNDED]);
        $collection->addExpressionFieldToSelect(
            'total_rewarded_sum',
            'sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0))',
            'total_rewarded_sum'
        );
        $this->_buildCollection($collection, $data, false);
        $collectionReward = $collection;

        /** Query to get total rewards per type */
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->addFieldToSelect('type_of_transaction');
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status_check', ['neq' => Status::REFUNDED]);
        $collection->addFieldToFilter('main_table.type_of_transaction', ['neq' => Type::USE_TO_CHECKOUT]);
        $collection->addExpressionFieldToSelect(
            'total_rewarded_sum',
            'sum(if(type_of_transaction IN ('.implode(",", $this->_useType).'),amount,0))',
            'total_rewarded_sum'
        );
        $collection->getSelect()->group(['type_of_transaction']);
        $this->_buildCollection($collection, $data);
        $total_rewarded_sum = $collectionReward->getFirstItem()->getData('total_rewarded_sum');
        $data = [];
        foreach ($this->_useType as $type) {
            $text = $this->_returnTextType($type);
            $data[$type] = [__($text), 0];
        }

        $_data = [
            'signup' => 0,
            'purchase' => 0,
            'birthday' => 0,
            'referal' => 0,
            'newsletter' => 0,
            'review' => 0,
            'tags' => 0,
            'social' => 0,
            'others' => 0
        ];
        foreach ($collection as $item) {
            if (in_array($item->getData('type_of_transaction'), $this->_groupSignup)) {
                $_data['signup'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupOrder)) {
                $_data['purchase'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupBirthday)) {
                $_data['birthday'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupReferal)) {
                $_data['referal'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupNewsletter)) {
                $_data['newsletter'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupReview)) {
                $_data['review'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupTag)) {
                $_data['tags'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupSocial)) {
                $_data['social'] += $item->getData('total_rewarded_sum');
            }

            if (in_array($item->getData('type_of_transaction'), $this->_groupOther)) {
                $_data['others'] += $item->getData('total_rewarded_sum');
            }
        }

        $data = [];
        foreach ($_data as $key => $value) {
            if ($total_rewarded_sum == 0) {
                $percent = 0;
            } else {
                $percent = $value / $total_rewarded_sum * 100;
            }

            if ($percent > 0.1) {
                $data[] = [__(ucfirst($key)), $percent];
            }
        }

        return json_encode($data);
    }

    /**
     * @return mixed
     */
    public function prepareCollectionMostUserPoint()
    {
        /**
         * Retrieve the read connection
         */
        $readConnection = $this->_appResource->getConnection('read');
        $query = "
            SELECT
                rwc.mw_reward_point, customer_id, @curRank := @curRank + 1 AS rank
            FROM ".$this->_appResource->getTableName('mw_reward_point_customer')." AS rwc
            LEFT JOIN ".$this->_appResource->getTableName('customer_entity')." AS ce ON rwc.customer_id = ce.entity_id, (SELECT @curRank := 0) r
            WHERE ce.entity_id > 0
            ORDER BY mw_reward_point DESC
            LIMIT 0, 5";

        /**
         * Execute the query and store the results in $results
         */
        $results = $readConnection->fetchAll($query);

        return $results;
    }

    /**
     * @param $collectionRedeemed
     * @param $collectionReward
     * @param $collectionOrder
     * @param $type
     * @param $rangeDate
     * @return array
     * @throws \Exception
     */
    protected function _buildResult($collectionRedeemed, $collectionReward, $collectionOrder, $type, $rangeDate)
    {
        $_data = [];

        try {
            if ($type == 'multiday') {
                foreach ($rangeDate as $year => $months) {
                    foreach ($months as $month => $days) {
                        foreach ($days as $day) {
                            $_data['report']['redeemed'][$year."-".$month."-".$day]  = [$year, $month, $day, 0];
                            $_data['report']['rewarded'][$year."-".$month."-".$day]  = [$year, $month, $day, 0];
                        }

                        foreach ($collectionRedeemed as $redeemd) {
                            if ($redeemd->getMonth() == $month) {
                                foreach ($days as $day) {
                                    if ($redeemd->getDay() == $day) {
                                        $_data['report']['redeemed'][$year."-".$month."-".$day]  = [$year, $month, $day, (int)$redeemd->getTotalRedeemedSum()];
                                    }
                                }
                            }
                        }

                        foreach ($collectionReward as $reward) {
                            if ($reward->getMonth() == $month) {
                                foreach ($days as $day) {
                                    if ($reward->getDay() == $day) {
                                        $_data['report']['rewarded'][$year."-".$month."-".$day]  = [$year, $month, $day, (int)$reward->getTotalRewardedSum()];
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                switch($type) {
                    case 'hour':
                        $rangeTempDate = reset($rangeDate);
                        $i = $rangeTempDate['incr_hour'];
                        break;
                    case 'day':
                        $rangeTempDate = reset($rangeDate);
                        $i = $rangeTempDate['count_day'];
                        break;
                    default:
                        $i = 0;
                }

                foreach ($rangeDate as $date) {
                    switch ($type) {
                        case 'hour':
                            $count = $date['native_hour'];
                            break;
                        case 'day':
                            $count = $date['native_day'];
                            break;
                        default:
                            $count = 0;
                    }

                    $_data['report']['redeemed'][$i] = 0;
                    $_data['report']['rewarded'][$i] = 0;
                    $_data['report']['order'][$i] = 0;

                    foreach ($collectionRedeemed as $redeemd) {
                        if ((int)$redeemd->{"get$type"}() == $count) {
                            if (isset($date['day']) && $date['day'] == (int)$redeemd->getDay()) {
                                $_data['report']['redeemed'][$i] = (int)$redeemd->getTotalRedeemedSum();
                            } else if (!isset($date['day'])) {
                                $_data['report']['redeemed'][$i] = (int)$redeemd->getTotalRedeemedSum();
                            }
                        }
                    }

                    foreach ($collectionReward as $reward) {
                        if ((int)$reward->{"get$type"}() == $count) {
                            if (isset($date['day']) && $date['day'] == (int)$reward->getDay()) {
                                $_data['report']['rewarded'][$i] = (int)$reward->getTotalRewardedSum();
                            } else if (!isset($date['day'])) {
                                $_data['report']['rewarded'][$i] = (int)$reward->getTotalRewardedSum();
                            }
                        }
                    }

                    foreach ($collectionOrder as $order) {
                        if ((int)$order->{"get$type"}() == $count) {
                            $_data['report']['order'][$i] = (int)$order->getTotalOrders();
                        }
                    }
                    $i++;
                }
            }

            if (isset($_data['report']['redeemed'])) {
                $_data['report']['redeemed'] = array_values($_data['report']['redeemed']);
            }
            if (isset($_data['report']['rewarded'])) {
                $_data['report']['rewarded'] = array_values($_data['report']['rewarded']);
            }
            if (isset($_data['report']['order'])) {
                $_data['report']['order'] = array_values($_data['report']['order']);
            }
        } catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }

        return $_data;
    }

    /**
     * @param $collection
     * @param $data
     * @param bool|true $group
     */
    protected function _buildCollection(&$collection, $data, $group = true)
    {
        switch ($data['report_range']) {
            case ReportRage::REPORT_RAGE_LAST_24H:
                /* Last 24h */
                $start_hour = date('Y-m-d h:i:s', strtotime('-1 day', (new \DateTime())->getTimestamp()));
                $start_hour = 'CONVERT_TZ(\'' . $start_hour . '\', \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $end_hour = date('Y-m-d h:i:s', strtotime("now"));
                $end_hour = 'CONVERT_TZ(\'' . $end_hour . '\', \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';

                if ($group == true) {
                    $collection->addExpressionFieldToSelect(
                        'hour',
                        'HOUR(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))',
                        'hour'
                    );
                    $collection->addExpressionFieldToSelect(
                        'day',
                        'DAY(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))',
                        'day'
                    );
                    $collection->getSelect()->group(['hour']);
                }

                $where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where . ' >= ' . $start_hour . ' AND ' . $where . ' <= ' . $end_hour);
                break;
            case ReportRage::REPORT_RAGE_LAST_WEEK:
                /* Last week */
                $start_day = date('Y-m-d',strtotime("-7 day", strtotime("Sunday Last Week")));
                $end_day = date('Y-m-d',strtotime("Sunday Last Week"));
                if ($group == true) {
                    $collection->addExpressionFieldToSelect('day', 'DAY(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'day');
                    $collection->getSelect()->group(['day']);
                }

                $where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where . ' >= "' . $start_day . '" AND ' . $where . ' <= "' . $end_day . '"');
                break;
            case ReportRage::REPORT_RAGE_LAST_MONTH:
                /* Last month */
                $last_month_time = $this->_getLastMonthTime();
                $last_month = date('m', strtotime($last_month_time));
                $start_day = date('Y', strtotime($last_month_time))."-".$last_month."-1";
                $end_day = date('Y', strtotime($last_month_time))."-".$last_month."-".$this->_days_in_month($last_month);

                /** Fix bug next one day */
                $end_day = strtotime($end_day.' +1 day');
                $end_day = date('Y', $end_day)."-".date('m', $end_day)."-".date('d', $end_day);

                if ($group == true) {
                    $collection->addExpressionFieldToSelect('day', 'DAY(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'day');
                    $collection->getSelect()->group(['day']);
                }

                $where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where . ' >= "' . $start_day . '" AND ' . $where . ' <= "' . $end_day . '"');
                break;
            case ReportRage::REPORT_RAGE_LAST_7DAYS:
            case ReportRage::REPORT_RAGE_LAST_30DAYS:
                /** Last X days */
                $last_x_day = 0;
                if ($data['report_range'] == ReportRage::REPORT_RAGE_LAST_7DAYS) {
                    $last_x_day = 7;
                } else if ($data['report_range'] == ReportRage::REPORT_RAGE_LAST_30DAYS) {
                    $last_x_day = 30;
                }
                $start_day = date('Y-m-d h:i:s', strtotime('-'.$last_x_day.' day', (new \DateTime())->getTimestamp()));
                $start_day = 'CONVERT_TZ(\'' . $start_day . '\', \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $end_day = date('Y-m-d h:i:s', strtotime("-1 day"));
                $end_day = 'CONVERT_TZ(\'' . $end_day . '\', \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                if ($group == true) {
                    $collection->getSelect()->group(['day']);
                }

                $collection->addExpressionFieldToSelect('month', 'MONTH(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'month');
                $collection->addExpressionFieldToSelect('day', 'DAY(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'day');
                $collection->addExpressionFieldToSelect('year', 'YEAR(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'year');

                $where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where . ' >= ' . $start_day . ' AND ' . $where . ' <= ' . $end_day);
                break;
            case ReportRage::REPORT_RAGE_CUSTOM:
                /* Custom range */
                if ($group == true) {
                    $collection->addExpressionFieldToSelect('month', 'MONTH(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'month');
                    $collection->addExpressionFieldToSelect('day', 'DAY(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'day');
                    $collection->addExpressionFieldToSelect('year', 'YEAR(CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\'))', 'year');
                    $collection->getSelect()->group(['day']);
                }

                $fromDate = date('Y-m-d H:i:s', strtotime(trim($data['from'])));
                $toDate = date('Y-m-d H:i:s', strtotime(trim($data['to'])));
                $where = 'CONVERT_TZ(main_table.transaction_time, \'+00:00\', \''.$this->_dataHelper->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where . ' >= "' . $fromDate . '" AND ' . $where . ' <= "' . $toDate . '"');
                break;
        }
    }

    /**
     * @return bool|string
     */
    protected function _getLastMonthTime()
    {
        return date('Y-m-d', strtotime("-1 month"));
    }

    /**
     * @param $type
     * @param int $from
     * @param int $to
     * @param null $original_time
     * @return array
     */
    protected function _buildArrayDate($type, $from = 0, $to = 23, $original_time = null)
    {
        $data = [];

        switch($type) {
            case ReportRage::REPORT_RAGE_LAST_24H:
                $start_day = $original_time['d'];
                for ($i = $from; $i <= $to; $i++) {
                    $data[$i]['incr_hour'] = $i;
                    $data[$i]['native_hour'] = ($i > 24) ? $i - 24 : $i;
                    $data[$i]['day'] = $start_day;

                    if ($i == 23) {
                        $start_day++;
                    }

                    if ($start_day > $this->_days_in_month($original_time['m'])) {
                        $start_day = 1;
                    }
                }
                break;
            case  ReportRage::REPORT_RAGE_LAST_WEEK:
                $data = [];
                $day_in_month = $this->_days_in_month(date('m'), date('Y'));
                $clone_from = $from;
                $reset = false;
                for ($i = 1; $i <=7; $i++) {
                    if ($from > $day_in_month && !$reset) {
                        $clone_from = 1;
                        $reset = true;
                    }
                    $data[$i]['count_day'] = $from;
                    $data[$i]['native_day'] = $clone_from;
                    $from++;
                    $clone_from++;
                }
                break;
            case  ReportRage::REPORT_RAGE_LAST_MONTH:
                for ($i = (int)$from; $i <= $to; $i++) {
                    $data[$i]['count_day'] = $from;
                    $data[$i]['native_day'] = (int)$i;
                }
                break;
            case  ReportRage::REPORT_RAGE_CUSTOM:
                $total_days = $this->_dateDiff($original_time['from'], $original_time['to']);
                if ($total_days <= 365) {
                    $all_months = $this->_get_months($original_time['from'], $original_time['to']);
                    $start_time = strtotime($original_time['from']);
                    $start_day  = (int)date('d', $start_time);
                    $year       = (int)date('Y', $start_time);
                    $count      = 0;
                    $data       = [];

                    $end_day_time = strtotime($original_time['to']);

                    $end_day = [
                        'm' => (int)date('m', $end_day_time),
                        'd' => (int)date('d', $end_day_time)
                    ];

                    foreach ($all_months as $month) {
                        $day_in_month = $this->_days_in_month($month, (int)date('Y', $start_time));

                        for ($day = ($count == 0 ? $start_day : 1); $day <= $day_in_month; $day++) {
                            if ($day > $end_day['d'] && $month == $end_day['m']) {
                                continue;
                            }
                            $data[$year][$month][$day] = $day;
                        }
                        $count++;

                        if ($month == 12) {
                            $year++;
                        }
                    }
                }
                break;
        }

        return $data;
    }

    /**
     * @param $month
     * @param null $year
     * @return int
     */
    protected function _days_in_month($month, $year = null)
    {
        $year = (!$year) ? date('Y', $this->_dateTime->gmtTimestamp()) : $year;
        $month = ($month == 2) ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);

        return $month;
    }

    /**
     * @param $d1
     * @param $d2
     * @return float
     */
    protected function _dateDiff($d1, $d2)
    {
        // Return the number of days between the two dates:
        return round(abs(strtotime($d1) - strtotime($d2))/86400);
    }

    /**
     * @param $data
     * @return bool
     */
    protected function _validationDate($data)
    {
        if (strtotime($data['from']) > strtotime($data['to'])) {
            return false;
        }

        return true;
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    protected function _get_months($start, $end)
    {
        $start = ($start == '') ? time() : strtotime($start);
        $end = ($end == '') ? time() : strtotime($end);
        $months = [];

        for ($i = $start; $i <= $end; $i = $this->get_next_month($i)) {
            $months[] = (int)date('m', $i);
        }

        return $months;
    }

    /**
     * @param $tstamp
     * @return int
     */
    protected function get_next_month($tstamp)
    {
        return (strtotime('+1 months', strtotime(date('Y-m-01', $tstamp))));
    }

    /**
     * @param $hour
     * @return int
     */
    protected function getPreviousDateTime($hour)
    {
        return (new \DateTime())->getTimestamp() - (3600 * $hour);
    }

    /**
     * @param $num
     * @return string
     */
    protected function convertNumberToMOnth($num)
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        return $months[$num];
    }

    /**
     * @param $type
     * @return mixed|string
     */
    protected function _returnTextType($type)
    {
        if (count($this->_constTypeRewardPoints) == 0) {
            $ref = new \ReflectionClass('MW\RewardPoints\Model\Type');
            $this->_constTypeRewardPoints = $ref->getConstants();
        }

        foreach ($this->_constTypeRewardPoints as $const => $value) {
            if ($type == $value) {
                $text = str_replace("_", " ", $const);
                $text = ucwords(strtolower($text));
                return $text;
            }
        }

        return '';
    }
}
