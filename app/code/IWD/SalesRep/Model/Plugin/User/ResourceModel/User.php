<?php

namespace IWD\SalesRep\Model\Plugin\User\ResourceModel;

/**
 * Class User
 * @package IWD\SalesRep\Model\Plugin\User\ResourceModel
 */
class User
{
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * User constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * AS FIX to call admin_user_delete_after, cause it is not dispatched
     * @param \Magento\User\Model\ResourceModel\User $resourceUser
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return mixed
     */
    public function aroundDelete(
        \Magento\User\Model\ResourceModel\User $resourceUser,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $user
    ) {
        $res = $proceed($user);
        if ($res) {
            $this->eventManager->dispatch('admin_user_delete_after', ['data_object' => $user]);
        }

        return $res;
    }
}
