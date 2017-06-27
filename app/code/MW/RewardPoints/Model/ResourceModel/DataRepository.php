<?php

namespace MW\RewardPoints\Model\ResourceModel;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class DataRepository implements \MW\RewardPoints\Api\DataRepositoryInterface
{
	/**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @var \MW\RewardPoints\Model\CatalogrulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_storeFactory = $storeFactory;
        $this->_customerFactory = $customerFactory;
        $this->_productFactory = $productFactory;
        $this->_dataHelper = $dataHelper;
        $this->_memberFactory = $memberFactory;
        $this->_catalogrulesFactory = $catalogrulesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerIdByEmail($email, $websiteId = null)
    {
        if ($email != '') {
            if ($websiteId == null) {
                $websiteId = $this->_websiteFactory->create()->load('base', 'code')->getId();
            }

            $customer = $this->_customerFactory->create()->setWebsiteId($websiteId);
            $customer = $customer->loadByEmail($email);
            if ($customer->getId()) {
                return __('Customer email (%1) has customer ID = %2', $email, $customer->getId())->__toString();
            } else {
                return __('Customer email (%1) is not avaiable', $email)->__toString();
            }
        } else {
            return __('Data (%1) is not avaiable', $email)->__toString();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBalanceByEmail($email, $websiteId = null)
    {
        if ($email != '') {
            if ($websiteId == null) {
                $websiteId = $this->_websiteFactory->create()->load('base', 'code')->getId();
            }

            $customer = $this->_customerFactory->create()->setWebsiteId($websiteId);
            $customer = $customer->loadByEmail($email);
            if ($customer->getId()) {
                $_customer = $this->_memberFactory->create()->load($customer->getId());
                if ($_customer->getId()) {
                    return __(
                        'Customer email (%1) has %2 reward points',
                        $email,
                        $_customer->getMwRewardPoint()
                    )->__toString();
                } else {
                    return __('Customer email (%1) has %2 reward points', $email, 0)->__toString();
                }
            } else {
                return __('Customer email (%1) is not avaiable', $email)->__toString();
            }
        } else {
            return __('Data (%1) is not avaiable', $email)->__toString();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updatePoints($id, $points, $comment)
    {
        if ($id && $points && $comment) {
            $customerId = (int) $id;
            $customer   = $this->_customerFactory->create()->load($customerId);
            if ($customer->getId()) {
                $this->_dataHelper->checkAndInsertCustomerId($customer->getId(), 0);
                $_customer = $this->_memberFactory->create()->load($customer->getId());
                $points    = (int) $points;
                if (is_numeric($points)) {
                    $oldPoints = $_customer->getMwRewardPoint();
                    $newPoints = $oldPoints + $points;

                    if ($newPoints < 0) {
                        $newPoints = 0;
                    }
                    $amount = abs($newPoints - $oldPoints);

                    if ($amount > 0) {
                        $_customer->setData('mw_reward_point', $newPoints);
                        $_customer->save();
                        $balance = $_customer->getMwRewardPoint();
                        if ($points > 0) {
                            $typeOfTransaction = Type::ADMIN_ADDITION;
                        } else {
                            $typeOfTransaction = Type::ADMIN_SUBTRACT;
                        }

                        $historyData = [
                            'type_of_transaction' => $typeOfTransaction,
                            'amount'              => $amount,
                            'balance'             => $balance,
                            'transaction_detail'  => $comment,
                            'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                            'status'              => Status::COMPLETE
                        ];
                        $_customer->saveTransactionHistory($historyData);

                        // Send mail when points changed
                        $store = $this->_storeFactory->create()->load($customer->getStoreId());
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $_customer->getId(),
                            $historyData,
                            $store->getCode()
                        );

                        return __(
                            'The customer ID (%1) (%2) updates point successfully. Current balance: %3 reward points',
                            $customerId,
                            $customer->getEmail(),
                            $balance
                        )->__toString();
                    }
                } else {
                    return __('%1 reward points must be numeric', $points)->__toString();
                }
            } else {
                return __('Customer ID (%1) is not avaiable', $customerId)->__toString();
            }
        } else {
            return __('Data is not avaiable')->__toString();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBalanceById($id)
    {
        if ($id) {
            $customerId = (int) $id;
            $customer   = $this->_customerFactory->create()->load($customerId);

            if ($customer->getId()) {
                $_customer = $this->_memberFactory->create()->load($customer->getId());

                if ($_customer->getId()) {
                    return __(
                        'Customer ID (%1) (%2) has %3 reward points',
                        $customer->getId(),
                        $customer->getEmail(),
                        $_customer->getMwRewardPoint()
                    )->__toString();
                } else {
                    return __(
                        'Customer ID (%1) (%2) has %3 reward points',
                        $customer->getId(),
                        $customer->getEmail(),
                        0
                    )->__toString();
                }
            } else {
                return __('Customer ID (%1) is not avaiable', $id)->__toString();
            }
        } else {
            return __('Data (%1) is not avaiable', $id)->__toString();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProductRewardPoints($sku)
    {
        if ($sku) {
            $sku       = trim($sku);
            $productId = $this->_productFactory->create()->getIdBySku($sku);

            if ($productId) {
                $mwRewardPoint = (int) $this->_catalogrulesFactory->create()->getPointCatalogRule($productId);

                return __('Product SKU (%1) has %2 reward points', $sku, $mwRewardPoint)->__toString();
            } else {
                return __('SKU (%1) is not avaiable', $sku)->__toString();
            }
        } else {
            return __('Data (%1) is not avaiable', $sku)->__toString();
        }
    }
}
