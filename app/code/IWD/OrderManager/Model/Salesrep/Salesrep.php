<?php

namespace IWD\OrderManager\Model\Salesrep;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;
use IWD\SalesRep\Model\Customer as SalesCustomer;
use IWD\SalesRep\Model\User as SalesUser;
use IWD\OrderManager\Model\Log\Logger;

class Salesrep extends AbstractModel
{
    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $user;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Salesrep constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\User\Model\UserFactory $user
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\User\Model\UserFactory $user,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->user = $user;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return bool
     */
    public function isSalesRepEnabled()
    {
        return $this->moduleManager->isEnabled('IWD_SalesRep') && $this->isOutputEnabled();
    }

    /**
     * @return bool
     */
    public function isOutputEnabled()
    {
        return !$this->scopeConfig->isSetFlag('advanced/modules_disable_output/IWD_SalesRep');
    }

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSalesReps()
    {
        $admins = [];

        if ($this->isSalesRepEnabled()) {
            $admins = $this->user->create()->getCollection();

            $salesrepUserTable = $this->resourceConnection->getTableName(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME);
            $admins->join(['salesrep_user' => $salesrepUserTable],
                'main_table.user_id = salesrep_user.' . SalesUser::ADMIN_ID,
                [
                    'salesrep_id' => 'salesrep_user.' . SalesUser::SALESREP_ID,
                    'salesrep_enabled' => 'salesrep_user.' . SalesUser::ENABLED,
                ]
            );
        }

        return $admins;
    }

    /**
     * @param $orderId
     * @return int
     */
    public function getCurrentSalesReps($orderId)
    {
        $salesrep = -1;

        if ($this->isSalesRepEnabled()) {
            $salesrepOrder = $this->getAssignedSalesrepOrder($orderId);
            $salesrep = $salesrepOrder->getSalesrepId();
        }

        return $salesrep;
    }

    /**
     * @param $orderId
     * @param $salesrepId
     * @param $customerId
     */
    public function updateSalesrep($orderId, $salesrepId, $customerId)
    {
        $salesrepOrder = $this->getAssignedSalesrepOrder($orderId);

        $oldSalesRepsId = $this->getCurrentSalesReps($orderId);
        $oldSalesReps = $this->getSalesRepById($oldSalesRepsId);
        $old = (empty($oldSalesReps) || empty($oldSalesReps->getId()))
            ? 'N/A'
            : $oldSalesReps->getFirstname() . ' ' . $oldSalesReps->getLastname();

        if ($salesrepId < 1) {
            $salesrepOrder->delete();
            $new = 'N/A';
            Logger::getInstance()->addChange('Sales Representative', $old, $new, 'order_info');
        } elseif ($salesrepId != $oldSalesRepsId) {
            $customer = $this->customerFactory->create()->load($customerId);
            $salesrepOrder
                ->setSalesrepId($salesrepId)
                ->setOrderId($orderId)
                ->setData(SalesCustomer::COMMISSION_APPLY_WHEN, $customer->getData(SalesCustomer::COMMISSION_APPLY_WHEN))
                ->setData(SalesCustomer::COMMISSION_RATE, $customer->getData(SalesCustomer::COMMISSION_RATE))
                ->setData(SalesCustomer::COMMISSION_TYPE, $customer->getData(SalesCustomer::COMMISSION_TYPE))
                ->save();
            $newSalesReps = $this->getSalesRepById($salesrepId);
            $new = $newSalesReps->getFirstname() . ' ' . $newSalesReps->getLastname();
            Logger::getInstance()->addChange('Sales Representative', $old, $new, 'order_info');
        }
    }

    public function getSalesRepById($id)
    {
        $admins = null;

        if ($this->isSalesRepEnabled()) {
            $admins = $this->user->create()->getCollection();
            $salesrepUserTable = $this->resourceConnection->getTableName(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME);
            $admins->join(['salesrep_user' => $salesrepUserTable],
                'main_table.user_id = salesrep_user.' . SalesUser::ADMIN_ID,
                [
                    'salesrep_id' => 'salesrep_user.' . SalesUser::SALESREP_ID,
                    'salesrep_enabled' => 'salesrep_user.' . SalesUser::ENABLED,
                ]
            );
            $admins = $admins->addFieldToFilter('entity_id', $id)
                ->setPageSize(1)
                ->getFirstItem();
        }

        return $admins;
    }

    /**
     * @param $orderId
     * @return \Magento\Framework\DataObject
     */
    protected function getAssignedSalesrepOrder($orderId)
    {
        $assignedOrder = $this->objectManager->create('\IWD\SalesRep\Model\OrderFactory');

        return $assignedOrder->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setPageSize(1)
            ->getFirstItem();
    }
}
