<?php

namespace IWD\SalesRep\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Salesrep
 * @package IWD\SalesRep\Block\Adminhtml\Order\View
 */
class Salesrep extends Template
{
    /**
     * @var \IWD\SalesRep\Model\OrderFactory
     */
    private $assignedOrder;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var null|\Magento\User\Model\User
     */
    private $adminUser = null;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Salesrep constructor.
     * @param Template\Context $context
     * @param ResourceConnection $resourceConnection
     * @param \IWD\SalesRep\Model\OrderFactory $assignedOrder
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ResourceConnection $resourceConnection,
        \IWD\SalesRep\Model\OrderFactory $assignedOrder,
        \Magento\User\Model\UserFactory $userFactory,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->assignedOrder = $assignedOrder;
        $this->userFactory = $userFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function hasSalesrep()
    {
        $this->loadAssignedSalesrep();
        return ($this->adminUser != null);
    }

    /**
     * @return string
     */
    public function getSalesrepName()
    {
        $this->loadAssignedSalesrep();
        return $this->adminUser->getFirstName() . ' ' . $this->adminUser->getLastName();
    }

    /**
     * @return \Magento\Framework\DataObject|null
     */
    private function loadAssignedSalesrep()
    {
        if ($this->adminUser == null) {
            $orderId = $this->getOrderId();

            $salesrepOrder = $this->assignedOrder->create()->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->setPageSize(1)
                ->getFirstItem();

            if ($salesrepOrder && !$salesrepOrder->isEmpty()) {
                $salesrepId = $salesrepOrder->getSalesrepId();
                $salesrepUserTable = $this->getConnection()->getTableName(\IWD\SalesRep\Model\ResourceModel\User::TABLE_NAME);

                $this->adminUser = $this->userFactory->create()->getCollection()
                    ->join(
                        ['salesrep_user' => $salesrepUserTable],
                        'main_table.user_id = salesrep_user.' . \IWD\SalesRep\Model\User::ADMIN_ID,
                        ['salesrep_id' => 'salesrep_user.' . \IWD\SalesRep\Model\User::SALESREP_ID]
                    )->addFieldToFilter('salesrep_user.' . \IWD\SalesRep\Model\User::SALESREP_ID, $salesrepId)
                    ->setPageSize(1)
                    ->getFirstItem();
            }
        }

        return $this->adminUser;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @return mixed
     */
    private function getOrderId()
    {
        return $this->getRequest()->getParam('order_id', 0);
    }
}
