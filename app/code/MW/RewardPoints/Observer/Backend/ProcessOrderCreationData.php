<?php

namespace MW\RewardPoints\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ProcessOrderCreationData implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\RewardPoints\Helper\Data $dataHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_messageManager = $messageManager;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $model   = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote   = $model->getQuote();
        if (isset($request['mw_rewardpoint_add']) && isset($request['customer_id'])) {
            $rewardpoints = (int)$request['mw_rewardpoint_add'];
            $customerId  = $request['customer_id'];

            if (isset($request['store_id'])) {
                $storeId = $request['store_id'];
            } else {
                $storeId = $this->_storeManager->getStore()->getId();
            }
            $store = $this->_storeFactory->create()->load($storeId);

            try {
                if ($rewardpoints < 0) {
                    $rewardpoints = -$rewardpoints;
                }

                if ($rewardpoints >= 0) {
                    $baseGrandTotal = $quote->getBaseGrandTotal();
                    $this->_dataHelper->setPointToCheckOut(
                        $rewardpoints,
                        $quote,
                        $customerId,
                        $store->getCode(),
                        $baseGrandTotal
                    );
                } else {
                    $this->_messageManager->addError(__('Cannot apply Reward Points'));
                }
            } catch (LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_messageManager->addException($e, __('Cannot apply Reward Points'));
            }
        }

        if (isset($request['mw_rewardpoint_remove'])) {
            try {
                $quote->setMwRewardpoint(0)
                    ->setMwRewardpointDiscount(0)
                    ->save();
            } catch (LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_messageManager->addException($e, __('Cannot remove Reward Points'));
            }
        }

        $quote->collectTotals()->save();

        return $this;
    }
}
