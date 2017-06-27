<?php

namespace MW\RewardPoints\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class SaveRewardPoints implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

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
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_messageManager = $messageManager;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
    }

    /**
     * Save reward points after saving customer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->moduleEnabled()) {
            return;
        }

        $request     = $observer->getEvent()->getRequest();
        $customer    = $observer->getEvent()->getCustomer();
        $customerId  = $customer->getId();
        $data        = $request->getPost();

        $this->_dataHelper->checkAndInsertCustomerId($customerId, 0);

        $_customer = $this->_memberFactory->create()->load($customerId);
        $oldPoints = $_customer->getMwRewardPoint();
        $amount    = $data['mw_reward_points_amount'];
        $action    = $data['mw_reward_points_action'];
        $comment   = $data['mw_reward_points_comment'];

        $newPoints = $oldPoints + $amount * $action;
        if ($newPoints < 0) {
            $newPoints = 0;
        }

        $amount = abs($newPoints - $oldPoints);
        if ($amount > 0) {
            $storeId = $this->_customerFactory->create()->load($customerId)->getStoreId();
            $storeCode = $this->_dataHelper->getStoreById($storeId)->getCode();
            $_customer->setData('mw_reward_point', $newPoints);
            $_customer->save();
            $balance = $_customer->getMwRewardPoint();

            $results           = $this->_dataHelper->getTransactionExpiredPoints($amount, $storeCode);
            $expiredDay        = $results[0];
            $expiredTime       = $results[1];
            $remainingPoints   = $results[2];
            $typeOftransaction = ($action > 0) ? Type::ADMIN_ADDITION : Type::ADMIN_SUBTRACT;

            $historyData = [
                'type_of_transaction' => $typeOftransaction,
                'amount'              => $amount,
                'balance'             => $balance,
                'transaction_detail'  => $comment,
                'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                'expired_day'         => $expiredDay,
                'expired_time'        => $expiredTime,
                'point_remaining'     => $remainingPoints,
                'status'              => Status::COMPLETE
            ];
            $_customer->saveTransactionHistory($historyData);

            // Process expired points when spent point
            if ($action < 0) {
                $this->_dataHelper->processExpiredPointsWhenSpentPoints($_customer->getId(), $amount);
            }

            // Send mail when points changed
            $this->_dataHelper->sendEmailCustomerPointChanged(
                $_customer->getId(),
                $historyData,
                $storeCode
            );

            $this->_messageManager->addSuccess(__('Reward points has successfully saved'));
        }
    }
}
