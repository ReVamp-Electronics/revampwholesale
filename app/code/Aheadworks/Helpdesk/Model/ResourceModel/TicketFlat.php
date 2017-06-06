<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel;

/**
 * Class TicketFlat
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 */
class TicketFlat extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Order repository model (by default)
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * User resource model
     *
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $userResource;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $userModelFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param \Magento\User\Model\UserFactory $userModelFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\User\Model\ResourceModel\User $userResource,
        \Magento\User\Model\UserFactory $userModelFactory,
        $connectionName = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->userResource = $userResource;
        $this->userModelFactory = $userModelFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_ticket_grid_flat', 'entity_id');
    }

    /**
     * Before save method. Add additional info in ticket flat
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $threadCollection = $object->getThread();
        $customerMsgCollection = clone $threadCollection;
        $customerMsgCollection->addCustomerTypeFilter()->load();

        $customerMessages = $customerMsgCollection->getSize();

        $agentMsgCollection = clone $threadCollection;
        $agentMsgCollection->addAgentTypeFilter()->load();

        $agentMessages = $agentMsgCollection->getSize();

        $lastReply = $threadCollection->getFirstItem();

        $lastReplyType = $lastReply->getType();
        $lastReplyDate = $lastReply->getCreatedAt();
        $lastReplyBy = $lastReply->getAuthorName();

        if (!$object->getFirstMessageContent()) {
            $firstReply = $threadCollection->getLastItem();
            $object->setFirstMessageContent($firstReply->getContent());
        }

        $agentName = __('Unassigned');
        $userModel = $this->userModelFactory->create();
        $this->userResource->load($userModel, $object->getAgentId());

        if ($userModel->getId()) {
            $agentName = $userModel->getName();
        }

        $orderIncrementId = __('Unassigned');
        try {
            $orderModel = $this->orderRepository->get($object->getOrderId());
        } catch (\Exception $e) {
            $orderModel = null;
        }

        if ($orderModel) {
            $orderIncrementId = $orderModel->getIncrementId();
        }

        if (!$orderModel && $object->getOrderIncrementId()) {
            $orderIncrementId = $object->getOrderIncrementId();
        }

        $object
            ->setCustomerMessages($customerMessages)
            ->setAgentMessages($agentMessages)
            ->setLastReplyType($lastReplyType)
            ->setLastReplyBy($lastReplyBy)
            ->setLastReplyDate($lastReplyDate)
            ->setAgentName($agentName)
            ->setOrderIncrementId($orderIncrementId);

        return parent::_beforeSave($object);
    }

}