<?php

namespace MW\RewardPoints\Helper;

use MW\RewardPoints\Model\Status;
use MW\RewardPoints\Model\Statusrule;

class Rules extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \MW\RewardPoints\Model\CatalogrulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory
    ) {
        parent::__construct($context);
        $this->_localeDate = $localeDate;
        $this->_customerFactory = $customerFactory;
        $this->_messageManager = $messageManager;
        $this->_catalogrulesFactory = $catalogrulesFactory;
        $this->_storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_historyFactory = $historyFactory;
        $this->_memberFactory = $memberFactory;
    }

    /**
     * Get catalog rule IDs
     *
     * @return array
     */
    public function getCatalogRules()
    {
        $catalogruleIds = [];

        // Check rule by enable
        $collection = $this->_catalogrulesFactory->create()->getCollection()
            ->addFieldToFilter('status', ['eq' => Statusrule::ENABLED]);

        if ($collection->count() > 0) {
            $position = [];
            foreach ($collection as $catalogrule) {
                // Check rule by time
                $startDate = $catalogrule->getStartDate();
                $endData = $catalogrule->getEndDate();

                if ($this->_localeDate->isScopeDateInInterval(null, $startDate, $endData)) {
                    // Push rule ID which is valid to array
                    $catalogruleIds[] = $catalogrule->getRuleId();
                    // Get positions
                    $position[] = (int) $catalogrule->getRulePosition();
                }
            }

            // Sort by positions
            if (sizeof($catalogruleIds) > 0) {
                array_multisort($position, $catalogruleIds);
            }
        }

        return $catalogruleIds;
    }

    /**
     * @param $product
     * @param $item
     * @param int $point
     */
    public function addCustomOptionPoint($product, $item, $point = 0)
    {
        $storeCode     = $this->_storeManager->getStore()->getCode();
        $mwRewardPoint = ($point) ? $point : $product->getMwRewardPointSellProduct();

        if ($mwRewardPoint > 0) {
            $infoArr = [];
            if ($info = $item->getProduct()->getCustomOption('info_buyRequest')) {
                $infoArr = unserialize($info->getValue());
            }

            $additionalOptions = [
                [
                    'code'        => 'sell_in_point',
                    'label'       => __('Sell in Points'),
                    'value'       => $this->_dataHelper->formatPoints($mwRewardPoint, $storeCode),
                    'print_value' => $this->_dataHelper->formatPoints($mwRewardPoint, $storeCode),
                    'orgi_value'  => $mwRewardPoint,
                ]
            ];
            $item->addOption(
                [
                    'code'  => 'additional_options',
                    'value' => serialize($additionalOptions),
                ]
            );

            // Add replacement additional option for reorder (see above)
            $infoArr['additional_options'] = $additionalOptions;

            $info->setValue(serialize($infoArr));
            $item->addOption($info);
        }
    }

    /**
     * @param $customerId
     * @param $typeOfTransaction
     * @param $ruleId
     * @param $store
     */
    public function processCustomRule($customerId, $typeOfTransaction, $ruleId, $store)
    {
        $transactions = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('type_of_transaction', $typeOfTransaction)
            ->addFieldToFilter('transaction_detail', $ruleId)
            ->addFieldToFilter('status', Status::COMPLETE);
        $customerGroupId = $this->_customerFactory->create()->load($customerId)->getGroupId();
        $results = $this->_activerulesFactory->create()->getPointByRuleId(
            $ruleId,
            $customerGroupId,
            $store->getId()
        );

        if (!sizeof($transactions)
            && $this->_dataHelper->checkCustomerMaxBalance($customerId, $store->getCode(), $results[0])
        ) {
            $this->_dataHelper->checkAndInsertCustomerId($customerId, 0);
            $_customer = $this->_memberFactory->create()->load($customerId);
            $points          = $results[0];
            $expiredDay      = $results[1];
            $expiredTime     = $results[2];
            $remainingPoints = $results[3];

            if ($points) {
                $_customer->addRewardPoint($points);
                $historyData = [
                    'type_of_transaction' => $typeOfTransaction,
                    'amount'              => $points,
                    'balance'             => $_customer->getMwRewardPoint(),
                    'transaction_detail'  => $ruleId,
                    'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                    'expired_day'         => $expiredDay,
                    'expired_time'        => $expiredTime,
                    'point_remaining'     => $remainingPoints,
                    'status'              => Status::COMPLETE
                ];
                $_customer->saveTransactionHistory($historyData);

                // Send mail when points changed
                $this->_dataHelper->sendEmailCustomerPointChanged(
                    $_customer->getId(),
                    $historyData,
                    $store->getCode()
                );
                $this->_messageManager->addSuccess(
                    __('Congratulation! %1 Reward Points have been added to your account', $points)
                );
            }
        }
    }
}
