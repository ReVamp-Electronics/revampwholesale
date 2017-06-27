<?php

namespace MW\RewardPoints\Cron;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;
use MW\RewardPoints\Model\Statusrule;

class AddRewardPointsForSpecialEvents
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_storeFactory = $storeFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
    }

    /**
     * Add reward points for special events
     */
    public function execute()
    {
        $ruleId       = 0;
        $month        = date('m', (new \DateTime())->getTimestamp());
        $day          = date('d', (new \DateTime())->getTimestamp());
        $year         = date('Y', (new \DateTime())->getTimestamp());
        $activePoints = $this->_activerulesFactory->create()->getCollection()
            ->addFieldToFilter('type_of_transaction', Type::SPECIAL_EVENTS)
            ->addFieldToFilter('date_event', ['like' => '%' . $year . '-' . $month . '-' . $day])
            ->addFieldToFilter('status', Statusrule::ENABLED);

        if (sizeof($activePoints) > 0) {
            foreach ($activePoints as $activePoint) {
                $points = (int) $activePoint->getRewardPoint();
                if ($points > 0) {
                    $ruleId = $activePoint->getRuleId();
                }
                break;
            }
        }

        if ($ruleId != 0) {
            $activeRule       = $this->_activerulesFactory->create()->load($ruleId);
            $rewardPoint      = (int) $activeRule->getRewardPoint();
            $storeView        = $activeRule->getStoreView();
            $comment          = $activeRule->getComment();
            $customerGroupIds = $activeRule->getCustomerGroupIds();

            $customer = $this->_customerFactory->create()->getCollection();
            $customer->addFieldToFilter('group_id', ['in' => [$customerGroupIds]]);
            $items = $customer->getItems();

            if (sizeof($items) > 0) {
                foreach ($items as $item) {
                    $expiredTime     = null;
                    $remainingPoint  = 0;
                    $storeId         = $item->getStoreId();
                    $customerId      = $item->getEntityId();
                    $checkStoreView  = $this->_activerulesFactory->create()->checkActiveRulesStoreView($storeView, $storeId);
                    $defaultExpired  = $activePoint->getDefaultExpired();
                    $expiredDay      = $activePoint->getExpiredDay();
                    $store           = $this->_storeFactory->create()->load($storeId);

                    if ($defaultExpired == 1) {
                        $expiredDay = (int) $this->_dataHelper->getExpirationDaysPoint($store->getCode());
                    }

                    if ($expiredDay > 0) {
                        $expiredTime    = time() + $expiredDay * 24 * 3600;
                        $remainingPoint = $rewardPoint;
                    }

                    if ($checkStoreView
                        && $rewardPoint > 0
                        && $this->_dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $rewardPoint)
                    ) {
                        $this->_dataHelper->checkAndInsertCustomerId($customerId, 0);
                        $_customer = $this->_memberFactory->create()->load($customerId);
                        $_customer->addRewardPoint($rewardPoint);
                        $historyData = [
                            'type_of_transaction' => Type::SPECIAL_EVENTS,
                            'amount'              => (int)$rewardPoint,
                            'balance'             => $_customer->getMwRewardPoint(),
                            'transaction_detail'  => $comment,
                            'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                            'expired_day'         => $expiredDay,
                            'expired_time'        => $expiredTime,
                            'point_remaining'     => $remainingPoint,
                            'status'              => Status::COMPLETE
                        ];

                        $_customer->saveTransactionHistory($historyData);

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $_customer->getId(),
                            $historyData,
                            $store->getCode()
                        );
                    }
                }
            }
        }
    }
}
